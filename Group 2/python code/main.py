from fetch_data import fetch_all_tables_data_to_df

dataframes_dict = fetch_all_tables_data_to_df()
    
if dataframes_dict is None:
    print("Failed to fetch data from the database.")

for table_name, df in dataframes_dict.items():
    print(f"Data for table '{table_name}':")
    print(df.head(), "\n")