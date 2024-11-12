import random
import mysql.connector
from faker import Faker

# Database connection
connection = mysql.connector.connect(
    host="localhost",          # e.g., "localhost"
    user="root",       # e.g., "root"
    password="",
    database="studentgradingsystem"    # Replace with your database name
)

cursor = connection.cursor()

# Using Faker to generate random names
fake = Faker()

# Generate 90 random records
for _ in range(90):
    lrn = random.randint(100000, 999999)  # Random LRN
    fname = fake.first_name()
    midname = fake.first_name()[0] + "."  # Initial for middle name
    lname = fake.last_name()
    suffix = random.choice(["", "Jr.", "Sr."])  # Randomly choose suffix
    gender = random.choice(["Male", "Female"])
    class_name = random.choice(["Grade 7", "Grade 8", "Grade 9", "Grade 10", "Grade 11", "Grade 12"])

    # Insert the generated data into the table
    query = """
        INSERT INTO StudentInfo (LRN, Fname, Midname, Lname, Suffix, Gender, Class)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
    """
    values = (lrn, fname, midname, lname, suffix, gender, class_name)

    cursor.execute(query, values)

# Commit the transaction
connection.commit()

print("90 random student records have been inserted successfully.")

# Close the connection
cursor.close()
connection.close()
