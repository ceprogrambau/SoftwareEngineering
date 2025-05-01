import mysql.connector
import json
from collections import defaultdict
from datetime import datetime


class Student:
    def __init__(self, id, name, gpa, completed_courses, total_credits):
        self.id = id
        self.name = name
        self.gpa = float(gpa) if gpa else 0.0
        self.completed_courses = completed_courses
        self.total_credits = total_credits

    def is_on_probation(self):
        return self.gpa < 2.0

class Course:
    def __init__(self, crn, course_code, name, credits, prerequisites, sessions, course_type):
        self.crn = crn
        self.course_code = course_code.strip().upper()
        self.name = name
        self.credits = credits
        self.prerequisites = prerequisites
        self.sessions = sessions
        self.course_type = course_type

    def conflicts_with(self, other):
        for s1 in self.sessions:
            for s2 in other.sessions:
                if s1["day"] == s2["day"]:
                    if max(s1["start"], s2["start"]) < min(s1["end"], s2["end"]):
                        return True
        return False

def create_connection():
    return mysql.connector.connect(
        host='shuttle.proxy.rlwy.net',
        port=28919,
        database='railway',
        user='root',
        password='tpwBLMcDhPGJKdTYmcMrBlJnMmIOvkjy'
    )

def fetch_course_catalog():
    conn = create_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT CourseCode, Title, Credits, Prerequisites, CourseType FROM course_catalog")
    catalog = {}
    for code, title, credits, prereq, ctype in cursor:
        catalog[code.strip().upper()] = {
            "title": title,
            "credits": credits,
            "prerequisites": json.loads(prereq) if prereq else {},
            "type": ctype
        }
    cursor.close()
    conn.close()
    return catalog

def fetch_course_offerings():
    conn = create_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT CRN, CourseCode, Instructor, TimeSlots, Room, SessionType, Capacity FROM course_offerings")
    raw_rows = cursor.fetchall()
    catalog = fetch_course_catalog()
    grouped = defaultdict(list)

    for crn, code, instr, time_json, room, session_type, capacity in raw_rows:
        if code.strip().upper() not in catalog or capacity == 0:
            continue
        timeslots = json.loads(time_json)
        for day, (start, end) in timeslots.items():
            grouped[crn].append({
                "day": day,
                "start": start,
                "end": end,
                "instructor": instr,
                "room": room,
                "type": session_type
            })

    course_map = {}
    cursor.execute("SELECT DISTINCT CRN, CourseCode FROM course_offerings")
    for crn, code in cursor.fetchall():
        course_map[crn] = code.strip().upper()

    offerings = []
    for crn, sessions in grouped.items():
        code = course_map.get(crn)
        if code not in catalog:
            continue
        data = catalog[code]
        offerings.append(Course(
            crn=crn,
            course_code=code,
            name=data["title"],
            credits=data["credits"],
            prerequisites=data["prerequisites"],
            sessions=sessions,
            course_type=data["type"]
        ))

    cursor.close()
    conn.close()
    return offerings

def fetch_study_plan():
    conn = create_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT course_code, semester FROM study_plan")
    plan = defaultdict(list)
    for course_code, semester in cursor.fetchall():
        plan[semester].append(course_code.strip().upper())
    cursor.close()
    conn.close()
    return plan

def calculate_current_semester(total_credits):
    now = datetime.now()
    month = now.month
    if month in [8, 9, 10, 11, 12]:
        season = 'Fall'
    elif month in [1, 2, 3, 4, 5]:
        season = 'Spring'
    else:
        season = 'Summer'

    thresholds = [0, 18, 36, 45, 63, 81, 90, 108, 126, 135, 150]
    approximated_semester = 1
    for i, credit_threshold in enumerate(thresholds):
        if total_credits < credit_threshold:
            approximated_semester = i
            break
    else:
        approximated_semester = 11
    print("Approximated Semester:", approximated_semester)
    if season == 'Fall':
        adjusted = (approximated_semester // 3) * 3 + 1
    elif season == 'Spring':
        adjusted = (approximated_semester // 3) * 3 + 2
    else:
        adjusted = (approximated_semester // 3) * 3 + 3

    if adjusted > 11:
        adjusted = 11
    print("Adjusted:", adjusted)
    return adjusted

def passes_prerequisites(course, completed):
    prereq = course.prerequisites
    return all(p in completed for p in prereq.get("prereq", [])) and \
           all(any(x in completed for x in group) for group in prereq.get("prereq_or", []))

def generate_schedule(student, all_courses):
    now = datetime.now()
    month = now.month
    if month in [8, 9, 10, 11, 12]:
        season = 'Fall'
    elif month in [1, 2, 3, 4, 5]:
        season = 'Spring'
    else:
        season = 'Summer'

    completed = [c.strip().upper() for c in student.completed_courses]
    max_credits = 12 if student.is_on_probation() else 18
    if season == 'Summer':
        max_credits = 9

    schedule = []
    total_credits = 0
    added_codes = set()
    alternatives = defaultdict(list)

    catalog = fetch_course_catalog()
    study_plan = fetch_study_plan()
    current_semester = calculate_current_semester(student.total_credits)
    print("Total Credits:", student.total_credits)

    total_technical_elective_credits = 0
    total_general_elective_credits = 0

    print("Completed: ",completed)
    for course_code in completed:
        if course_code in catalog:
            course = catalog[course_code]
            if course["type"] == "Technical Elective":
                total_technical_elective_credits += course["credits"]
            elif course["type"] == "General Elective":
                total_general_elective_credits += course["credits"]

    max_technical_credits = 12
    max_general_credits = 8

    needed_courses = []
    for sem in range(1, current_semester + 1):
        for course_code in study_plan.get(sem, []):
            if course_code == "GENERAL ELECTIVE" or course_code == "TECHNICAL ELECTIVE":
                for code, course in catalog.items():
                    if course['type'] in ["General Elective", "Technical Elective"]:
                        needed_courses.append(code)
            elif course_code not in completed:
                needed_courses.append(course_code)
    print ("Need Courses", needed_courses)
    def course_priority(c):
        weight = 0
        if catalog[c.course_code]["type"] == "Major":
            weight -= 10
        if catalog[c.course_code]["type"] == "Technical Elective":
            weight -= 8
        if catalog[c.course_code]["type"] == "Engineering Core":
            weight -= 5
        for other in catalog.values():
            if c.course_code in other.get("prerequisites", {}).get("prereq", []):
                weight -= 8
        return weight

    all_courses.sort(key=course_priority)

    course_grouped = defaultdict(list)
    for course in all_courses:
        course_grouped[course.course_code].append(course)

    def try_add_course(course):
        nonlocal total_credits, total_technical_elective_credits, total_general_elective_credits

        if course.course_code in added_codes:
            return False

        if any(course.conflicts_with(c) for c in schedule):
            return False

        if total_credits + course.credits > max_credits:
            return False

        conn = create_connection()
        cursor = conn.cursor()

        try:
            cursor.execute("SELECT Capacity FROM course_offerings WHERE CRN = %s", (course.crn,))
            result = cursor.fetchall()
            if not result or result[0][0] == 0:
                return False

        except mysql.connector.Error:
            return False

        finally:
            cursor.close()
            conn.close()

        if course.course_code not in completed:
            if course.course_type == "Technical Elective" and total_technical_elective_credits >= max_technical_credits:
                return False
            if course.course_type == "General Elective" and total_general_elective_credits >= max_general_credits:
                return False

        schedule.append(course)
        added_codes.add(course.course_code)
        total_credits += course.credits

        if course.course_type == "Technical Elective":
            total_technical_elective_credits += course.credits
        elif course.course_type == "General Elective":
            total_general_elective_credits += course.credits

        return True

    for course in all_courses:
        if course.course_code not in needed_courses:
            continue
        if course.course_code in completed or course.course_code in added_codes:
            continue
        if not passes_prerequisites(course, completed):
            continue
        if try_add_course(course):
            for alt_course in course_grouped.get(course.course_code, []):
                if alt_course.crn != schedule[-1].crn:
                    alternatives[course.course_code].append(alt_course)
    return schedule, alternatives

def get_schedule_for_student(student_id, completed_courses, sgpa, total_credits):
    return generate_schedule(
        Student(student_id, "Student Name", sgpa, completed_courses, total_credits),
        fetch_course_offerings()
    )
