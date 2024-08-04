<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch tasks assigned to the user
$tasks_query = "SELECT tasks.*, user_tasks.assignment_date FROM tasks
                JOIN user_tasks ON tasks.id = user_tasks.task_id
                WHERE user_tasks.user_id = ?";
$stmt = $conn->prepare($tasks_query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $taskAssignedDate = new DateTime($row['assignment_date']);
    $currentDate = new DateTime();
    $interval = $currentDate->diff($taskAssignedDate);
    $daysPassed = $interval->days;

    $row['can_complete'] = $daysPassed >= 1; // Enable completion if one day has passed
    $tasks[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Space Journey</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        #scene {
            width: 100vw;
            height: 100vh;
            display: block;
        }
        #task-dialog {
            position: fixed;
            top: 20%;
            left: 20%;
            width: 60%;
            padding: 20px;
            background-color: white;
            border: 2px solid black;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1000;
        }
        #task-dialog h2 {
            margin-top: 0;
        }
        .button {
            margin: 10px;
            padding: 10px 20px;
            background-color: #667BC6;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="scene"></div>

    <!-- Task Dialog -->
    <div id="task-dialog">
        <h2>Tasks</h2>
        <div id="task-list">
            <!-- Tasks will be dynamically inserted here -->
        </div>
        <button class="button" onclick="closeTaskDialog()">Close</button>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Initialize Three.js scene
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer();
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById('scene').appendChild(renderer.domElement);

        // Create a round planet
        const planetGeometry = new THREE.SphereGeometry(1, 32, 32);
        const planetMaterial = new THREE.MeshBasicMaterial({ color: 0x0077ff });
        const planet = new THREE.Mesh(planetGeometry, planetMaterial);
        scene.add(planet);

        // Create stars
        const starGeometry = new THREE.SphereGeometry(0.05, 24, 24);
        const starMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff });
        for (let i = 0; i < 1000; i++) {
            const star = new THREE.Mesh(starGeometry, starMaterial);
            star.position.set(
                Math.random() * 200 - 100,
                Math.random() * 200 - 100,
                Math.random() * 200 - 100
            );
            scene.add(star);
        }

        camera.position.z = 5;

        function animate() {
            requestAnimationFrame(animate);
            planet.rotation.y += 0.01;
            renderer.render(scene, camera);
        }
        animate();

        // Handle planet click
        function onPlanetClick() {
            document.getElementById('task-dialog').style.display = 'block';

            // Insert tasks into the dialog
            const taskList = document.getElementById('task-list');
            taskList.innerHTML = '';
            <?php foreach ($tasks as $task): ?>
                const taskElement = document.createElement('div');
                taskElement.innerHTML = `
                    <h3><?php echo $task['task_name']; ?></h3>
                    <p><?php echo $task['task_description']; ?></p>
                    <?php if ($task['can_complete']): ?>
                        <button class="button" onclick="completeTask(<?php echo $task['id']; ?>)">Complete</button>
                    <?php else: ?>
                        <p>Complete button will appear one day after task is assigned.</p>
                    <?php endif; ?>
                `;
                taskList.appendChild(taskElement);
            <?php endforeach; ?>
        }

        function closeTaskDialog() {
            document.getElementById('task-dialog').style.display = 'none';
        }

        function completeTask(taskId) {
            // AJAX request to complete the task
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'complete_task.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('task_id=' + taskId);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Task completed!');
                    document.getElementById('task-dialog').style.display = 'none';
                } else {
                    alert('Error completing task.');
                }
            };
        }

        // Adding click event to planet (for demo purposes, actual event listener should be added in your code)
        planet.addEventListener('click', onPlanetClick);
    </script>
</body>
</html>
