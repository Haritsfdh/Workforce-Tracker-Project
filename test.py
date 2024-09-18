import cv2
import time
import torch
import datetime
import mysql.connector
import base64
import numpy as np

# Define the path to the YOLOv5 repo and weight file
repo_path = "yolov5"  # Change this to the actual path
weight_path = "best.pt"  # Change this to the actual path

# Load YOLOv5 with your trained weight file from the local repo
model = torch.hub.load(repo_path, 'custom', path=weight_path, source='local', force_reload=True)

# Set device
device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
model.to(device)

# Connect to MySQL database
db_host = "localhost"
db_user = "root"
db_password = ""
db_name = "workforce_tracker"

try:
    connection = mysql.connector.connect(
        host=db_host,
        database=db_name,
        user=db_user,
        password=db_password
    )
    if connection.is_connected():
        print('Connected to MySQL database')
except mysql.connector.Error as e:
    print(f'Error: {e}')
    connection = None

if connection is None:
    raise Exception("Failed to connect to the database")

# Load the webcam
cam = cv2.VideoCapture(0)  # Ensure camera index is correct
if not cam.isOpened():
    raise Exception("No Camera")

# Function to convert image to binary
def convert_image_to_binary(image):
    _, buffer = cv2.imencode('.jpg', image)
    return base64.b64encode(buffer).decode('utf-8')

# Main loop
while True:
    ret, image = cam.read()
    if not ret:
        break
    
    _time_mulai = time.time()
    
    # Resize frame to match model input size
    resized_image = cv2.resize(image, (640, 480))  # You can adjust the size if needed
    
    # Perform inference
    results = model(resized_image)
    
    # Convert results to a format suitable for OpenCV
    rendered_image = results.render()[0]  # results.render() returns a list
    
    # Ensure rendered_image is writable
    rendered_image = np.array(rendered_image, copy=True)
    
    # Get objects detected
    objects = results.xyxy[0].cpu().numpy()
    objects_str = str(objects.tolist())
    
    # Check if any objects are detected
    if len(objects) > 0:
        # Get timestamp
        timestamp = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        cv2.putText(rendered_image, timestamp, (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
        # Convert image to binary
        image_binary = convert_image_to_binary(rendered_image)
        
        # Insert data into MySQL database
        try:
            cursor = connection.cursor()
            query = "INSERT INTO deteksiyolo (timestamp, image, objects) VALUES (%s, %s, %s)"
            cursor.execute(query, (timestamp, image_binary, objects_str))
            connection.commit()
            cursor.close()
            print('Data inserted into MySQL database')
        except mysql.connector.Error as e:
            print(f'Error: {e}')
    
    # Display results
    cv2.imshow('YOLOv5 Detection', rendered_image)
    
    print("Processing time:", time.time() - _time_mulai)
    
    # Check for 'q' key press to exit
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

# Release the camera and close OpenCV windows
cam.release()
cv2.destroyAllWindows()
if connection.is_connected():
    connection.close()
    print('MySQL database connection closed')
