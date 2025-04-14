import algorithm
import fetch_data as fd
import time
def tabulate(timetable):
    # Group entries by day
    days = ['M', 'T', 'W', 'H']
    day_schedules = {day: [] for day in days}
    
    # Sort entries by day and time
    for course_code, (start_time, end_time, room) in timetable.items():
        day = start_time.split()[0]  
        if day in day_schedules:
            day_schedules[day].append((start_time, end_time, course_code, room))
    for day in days:
        day_schedules[day].sort(key=lambda x: x[0])
    
    # Print the schedule
    print("\nWeekly Schedule:")
    print("=" * 100)
    
    for day in days:
        print(f"\n{day} Schedule:")
        print("-" * 100)
        print(f"{'Time':<20} {'Course':<15} {'Room':<10}")
        print("-" * 100)
        
        for start_time, end_time, course_code, room in day_schedules[day]:
            time_slot = f"{start_time} - {end_time}"
            print(f"{time_slot:<20} {course_code:<15} {room:<10}")
        print("-" * 100)

courses_df = fd.fetch_table('course')
rooms_df = fd.fetch_table('classroom')
professors_df = fd.fetch_table('doctors')
student_df = fd.fetch_table('student')

start_time = time.time()
population = algorithm.initialize_timetable_population(1    , courses_df, rooms_df,student_df)
end_time = time.time()
print(f"Time taken: {end_time - start_time}")

for timetable in population:
    x = algorithm.calculate_fitness(timetable, rooms_df, courses_df, professors_df, student_df)
    if x == 1:
        print(1)
