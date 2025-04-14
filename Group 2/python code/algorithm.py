import random
import fetch_data as fd
from collections import defaultdict
import logging
import math

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def generate_time_slots():
    days = ['M', 'T', 'W', 'H']
    time_slots = []
    for day in days:
        for hour in range(8, 16):  
            time_slots.append(f"{day} {hour}:00")
            time_slots.append(f"{day} {hour}:30")
        time_slots.append(f"{day} {16}:00")    
    return time_slots

def initialize_timetable_population(pop_size, courses_df, rooms_df, student_df):
    timeslots = generate_time_slots()
    days = ['M', 'T', 'W', 'H']
    timeslots_by_day = {}
    for day in days:
        day_slots = [slot for slot in timeslots if slot.startswith(day)]
        timeslots_by_day[day] = day_slots[:-4]#all days and times except the last 4

    room_for_lec = rooms_df[rooms_df['equipment'] == 'Lecture']
    room_for_lab = rooms_df[rooms_df['equipment'] != 'Lecture']

    population = []
    for _ in range(pop_size):
        timetable = {}
        courses = courses_df[courses_df['cType'] == "CE core"]
        for x, course in courses.iterrows():                      
            #assign first lecture
            day = random.choice(days)  # First choose a random day
            start_timeslot = random.choice(timeslots_by_day[day])  # Then choose a random timeslot from that day
            assigned_room = random.choice(room_for_lec['classCode'].tolist())     
            end_timeslot = timeslots[timeslots.index(start_timeslot) + int(course['lec1duration']/30)]
            timetable[course['courseCode']] = (start_timeslot, end_timeslot, assigned_room)
            
            #check if the course has a second lecture
            if course['singleLec'] == 0:
                if day == 'M' or day == "T":
                    day = days[days.index(day) + 2]
                else:
                    day = days[days.index(day) - 2]
                # Extract the time part from the first lecture's timeslot
                time_part = start_timeslot.split()[1]
                start_timeslot2 = f"{day} {time_part}"
                end_timeslot2 = timeslots[timeslots.index(start_timeslot2) + int(course['lec2duration']/30)]

                timetable[course['courseCode']+'_lec2'] = (start_timeslot2, end_timeslot2, assigned_room)

            #check if the course has a lab
            if course['has_lab'] == 1:
                year = course['aYear']  
                student_number = int(student_df[student_df['year'] == str(year)]['studentQuantity'].iloc[0])
                number_of_sections = math.ceil(student_number/24)
                for i in range(number_of_sections):
                    lab_day = random.choice(days)
                    start_timeslot = random.choice(timeslots_by_day[lab_day])
                    rooms_for_this_lab = room_for_lab[room_for_lab['equipment'] == course['lab_equipment']]
                    assigned_room = random.choice(rooms_for_this_lab['classCode'].tolist())
                    end_timeslot = timeslots[timeslots.index(start_timeslot) + int(course['labDuration']/30)]
                    timetable[course['courseCode']+'_lab'+str(i)] = (start_timeslot, end_timeslot, assigned_room)
             
        population.append(timetable)
    
    return population

def calculate_fitness(timetable, rooms, courses_df, professors_df, student_df):
    total_score = 1
    max_score = 0

    timeslots = generate_time_slots()
    
    courses = {row['courseCode']: row for _, row in courses_df.iterrows()}
    professors = {row['docID']: row for _, row in professors_df.iterrows()}
    rooms_dict = {row['classCode']: row for _, row in rooms.iterrows()}
    
    student_quantities = {row['year']: row['studentQuantity'] for _, row in student_df.iterrows()}
    
    professor_schedule = defaultdict(set)
    room_schedule = defaultdict(set)
    
    # Track courses in each room
    courses_by_room = defaultdict(list)
    for course_code, (start_time, end_time, room) in timetable.items():
        courses_by_room[room].append(course_code)
    # Check for room conflicts (same room, overlapping times)
    for room, course_list in courses_by_room.items():
        for i in range(len(course_list)):
            for j in range(i + 1, len(course_list)):
                course1 = course_list[i]
                course2 = course_list[j]
                
                start1, end1, _ = timetable[course1]
                start2, end2, _ = timetable[course2]
                
                # Check if courses are on same day
                if start1.split()[0] == start2.split()[0]:
                    # Get indices in timeslots list
                    start1_idx = timeslots.index(start1)
                    end1_idx = timeslots.index(end1)
                    start2_idx = timeslots.index(start2)
                    end2_idx = timeslots.index(end2)
                    
                    # Check for overlap
                    if (start1_idx <= start2_idx < end1_idx) or (start2_idx <= start1_idx < end2_idx):
                        total_score = 0
                        print(start1, end1, start2, end2)
    return total_score


def crossover(parent1, parent2):
    child = {}
    for course_code in parent1.keys():
        if course_code in parent2 and random.random() > 0.5:
            child[course_code] = parent2[course_code]
        else:
            child[course_code] = parent1[course_code]
    return child

def mutate(timetable, rooms_df):
    if not timetable:
        return
    course_code = random.choice(list(timetable.keys()))
    timeslot, end_timeslot, room = timetable[course_code]

    if random.random() > 0.5:
        slots = ["00", "30"]
        day = timeslot.split()[0]
        new_hour = random.randint(8, 15)
        new_slot = random.choice(slots)
        new_timeslot = f"{day} {new_hour}:{new_slot}"
        new_end_timeslot = str(new_hour + (int(end_timeslot.split()[1]) - int(timeslot.split()[1]) + int(new_timeslot.split()[1]))) + new_slot
        timetable[course_code] = (new_timeslot, room, new_end_timeslot)
    else:
        new_room = random.choice(rooms_df['classCode'].tolist())
        timetable[course_code] = (timeslot, new_room, end_timeslot)

def evaluate_population(population, rooms, courses_df, professors_df, student_df):
    evaluated_population = []
    for timetable in population:
        fitness_score = calculate_fitness(timetable, rooms, courses_df, professors_df, student_df)
        evaluated_population.append((timetable, fitness_score))
    evaluated_population.sort(key=lambda x: x[1], reverse=True)
    return evaluated_population

def evolve_population(population, rooms, courses_df, professors_df, student_df):
    threshold = 0.9
    generation = 0
    
    while True:
        logging.info(f"Generation {generation}: Evolving population...")
        evaluated_population = evaluate_population(population, rooms, courses_df, professors_df, student_df)
        
        avg_fitness = sum(fitness for _, fitness in evaluated_population) / len(evaluated_population)
        logging.info(f"Generation {generation}: Average fitness = {avg_fitness:.2f}")
        if avg_fitness >= threshold:
            logging.info(f"Threshold met! Average fitness = {avg_fitness:.2f}")
            break
        
        top_individuals = evaluated_population[:len(evaluated_population)//2]
        
        new_population = []
        for i in range(len(population)):
            parent1, parent2 = random.sample(top_individuals, 2)
            child = crossover(parent1[0], parent2[0])
            mutate(child,rooms)
            new_population.append(child)
        
        population = new_population
        generation += 1
    
    return population
