import random
import fetch_data as fd
from collections import defaultdict

def generate_time_slots():
    time_slots = []
    for day in ['M', 'T', 'W', 'T']:
        for hour in range(8, 16):  
            time_slots.append(f"{day} {hour}:00")
            time_slots.append(f"{day} {hour}:30")
    return time_slots

def initialize_timetable_population(pop_size, courses_df, rooms_df):
    timeslots = generate_time_slots()
    courses_with_lab = courses_df[courses_df['has_lab'] == 1]
    courses_without_lab = courses_df[courses_df['has_lab'] == 0]
    room_for_lec = rooms_df[rooms_df['equipment'] == 'Lecture']
    room_for_lab = rooms_df[rooms_df['equipment'] != 'Lecture']
    population = []
    for _ in range(pop_size):
        timetable = {}
        for x,course in courses_without_lab.iterrows():
            # start_timeslot = random.choice(timeslots)
            # assigned_room = random.choice(room_for_lec['classCode'].tolist())
            # end_timeslot = timeslots[timeslots.index(start_timeslot) + course[course['lec1duration']]]
            # timetable[course['courseCode']] = (start_timeslot, assigned_room)
            print(course)
        # for course in courses_with_lab:
            # start_timeslot = random.choice(timeslots)
            # assigned_room = random.choice(room_for_lec['classCode'].tolist())
            # end_timeslot = timeslots[timeslots.index(start_timeslot) + course[course['lec1duration']]]
            # timetable[course['courseCode']] = (start_timeslot, assigned_room)
            # start_timeslot = random.choice(timeslots)
            # assigned_room = random.choice(room_for_lab['classCode'].tolist())
            # timetable[course['courseCode']+'_lab'] = (start_timeslot, assigned_room)
        population.append(timetable)
    
    return population

def calculate_fitness(timetable, rooms, courses_df, professors_df, student_df):
    """
    Calculate the fitness of a timetable based on various constraints.
    Returns a score between 0 and 1, where 1 is perfect.
    """
    total_score = 0
    max_score = 0
    
    # Convert to dictionaries for easier access
    courses = {row['courseCode']: row for _, row in courses_df.iterrows()}
    professors = {row['docID']: row for _, row in professors_df.iterrows()}
    rooms_dict = {room['classCode']: room for room in rooms}
    
    # Assume each course has 60 students
    student_quantity = 60
    
    # Track professor schedules and room usage
    professor_schedule = defaultdict(set)
    room_schedule = defaultdict(set)
    
    # Check each course assignment
    for course_code, (timeslot, room_code) in timetable.items():
        course = courses[course_code]
        room = rooms_dict.get(room_code)
        
        if not room:
            continue  # Skip invalid room assignments
        
        # 1. Check room capacity (weight: 0.3)
        if student_quantity <= room['capacity']:
            total_score += 0.3
        
        # 2. Check room equipment requirements (weight: 0.2)
        if not course.get('equipmentRequired') or room.get('equipment'):
            total_score += 0.2
        
        # 3. Check professor availability (weight: 0.3)
        professor_code = course.get('professorCode')
        if professor_code and timeslot not in professor_schedule[professor_code]:
            total_score += 0.3
        
        # 4. Check room availability (weight: 0.2)
        if timeslot not in room_schedule[room_code]:
            total_score += 0.2
        
        # Add to tracking sets
        if professor_code:
            professor_schedule[professor_code].add(timeslot)
        room_schedule[room_code].add(timeslot)
        
        max_score += 1.0  # Maximum possible score per course
    
    # Return normalized score between 0 and 1
    return total_score / max_score if max_score > 0 else 0

def evaluate_population(population, rooms, courses_df, professors_df):
    """
    Evaluate the entire population and return sorted list of (timetable, fitness) tuples
    """
    evaluated_population = []
    for timetable in population:
        fitness = calculate_fitness(timetable, rooms, courses_df, professors_df)
        evaluated_population.append((timetable, fitness))
    
    # Sort by fitness (higher is better)
    return sorted(evaluated_population, key=lambda x: x[1], reverse=True)
