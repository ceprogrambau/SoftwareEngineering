import algorithm
import fetch_data as fd

courses_df = fd.fetch_table('course')
rooms_df = fd.fetch_table('classroom')
professors_df = fd.fetch_table('doctors')
student_df = fd.fetch_table('student')

population = algorithm.initialize_timetable_population(1, courses_df, rooms_df)

# Print each timetable's assignments on separate lines
for timetable in population:
    print("\nTimetable:")
    for course, (timeslot, room) in timetable.items():
        print(f"Course: {course}, Time: {timeslot}, Room: {room}")
