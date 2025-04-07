import random
import fetch_data as fd

def generate_time_slots():
    time_slots = []
    for day in ['M', 'T', 'W', 'T']:
        for hour in range(8, 16):  
            time_slots.append(f"{day} {hour}:00")
            time_slots.append(f"{day} {hour}:30")
    return time_slots

def initialize_timetable_population(pop_size):
    courses = fd.fetch_table('course')['courseCode'].tolist()
    rooms = fd.fetch_table('classroom')['classCode'].tolist()
    timeslots = generate_time_slots()
    population = []
    for _ in range(pop_size):
        timetable = {}
        for course in courses:
            assigned_timeslot = random.choice(timeslots)
            assigned_room = random.choice(rooms)
            timetable[course] = (assigned_timeslot, assigned_room)
        population.append(timetable)
    return population
