<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat - MedConsult</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .chat-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chat-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .chat-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 12px;
            max-width: 80%;
            animation: fadeIn 0.3s ease-in;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-left: auto;
            text-align: left;
        }

        .bot-message {
            background: white;
            border: 1px solid #e9ecef;
            color: #333;
        }

        .message strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            opacity: 0.8;
        }

        .chat-input {
            display: flex;
            padding: 25px;
            background: white;
            border-top: 1px solid #e9ecef;
        }

        .chat-input input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            margin-right: 15px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input input:focus {
            border-color: #667eea;
        }

        .chat-input button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chat-input button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .chat-input button:active {
            transform: translateY(0);
        }

        .login-prompt {
            text-align: center;
            padding: 80px 30px;
            background: white;
            border-radius: 15px;
            margin: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .login-prompt h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 32px;
        }

        .login-prompt p {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .login-prompt a {
            display: inline-block;
            margin: 15px;
            padding: 15px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-prompt a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .login-prompt a:active {
            transform: translateY(0);
        }

        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .welcome-message h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        .welcome-message ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .welcome-message li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <span>MedConsult</span>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): 
                    $userType = $_SESSION['user_type'] ?? 'patient';
                    if ($userType === 'doctor'): ?>
                        <li><a href="/Medical consultation system/doctor-dashboard.php">Doctor Dashboard</a></li>
                        <li><a href="/Medical consultation system/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/Medical consultation system/index.php">Home</a></li>
                        <li><a href="/Medical consultation system/find-doctors.php">Find Doctors</a></li>
                        <li><a href="/Medical consultation system/chat.php">AI Chat</a></li>
                        <li><a href="/Medical consultation system/dashboard.php">Dashboard</a></li>
                        <li><a href="/Medical consultation system/my-appointments.php">My Appointments</a></li>
                        <li><a href="/Medical consultation system/logout.php">Logout</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="/Medical consultation system/index.php">Home</a></li>
                    <li><a href="/Medical consultation system/find-doctors.php">Find Doctors</a></li>
                    <li><a href="/Medical consultation system/chat.php">AI Chat</a></li>
                    <li><a href="/Medical consultation system/login-form.php">Login</a></li>
                    <li class="dropdown">
                        <a href="#" class="btn-register">Register <i class="fas fa-caret-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="/Medical consultation system/register-form.php">Register as Patient</a></li>
                            <li><a href="/Medical consultation system/doctor-register-form.php">Register as Doctor</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="chat-container">
        <div class="chat-header">
            <h2>AI Medical Assistant</h2>
            <p>Ask me about doctors, appointments, or medical questions</p>
        </div>

        <?php if ($isLoggedIn): ?>
            <div class="chat-messages" id="chatMessages">
                <div class="welcome-message">
                    <h3>Welcome to MedConsult AI Assistant!</h3>
                    <p>I'm here to help you with medical information, doctor details, appointment scheduling, and general health queries. You can ask me about:</p>
                    <ul>
                        <li>Available doctors and their specializations</li>
                        <li>Doctor schedules and appointment availability</li>
                        <li>General medical information and health tips</li>
                        <li>Appointment booking guidance</li>
                        <li>Basic symptom inquiries</li>
                    </ul>
                    <p>How can I assist you today?</p>
                </div>
                <div class="message bot-message">
                    <strong>AI Assistant:</strong>
                    Hello! I'm your AI Medical Assistant. How can I help you today?
                </div>
            </div>
            <div class="chat-input">
                <input type="text" id="userInput" placeholder="Ask me about doctors, appointments, or health questions..." onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()">Send</button>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <h2>Welcome to MedConsult AI Chat</h2>
                <p>Please log in to access our AI Medical Assistant. You can ask questions about doctors, appointments, medical conditions, and more.</p>
                <a href="/Medical consultation system/login-form.php">Login</a>
                <a href="/Medical consultation system/register-form.php">Register</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isLoggedIn): ?>
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');

        // Sample doctor data for AI responses
        const doctorData = {
            cardiologist: {
                doctors: ['Dr. Smith', 'Dr. Johnson'],
                timings: '9:00 AM - 5:00 PM',
                availability: 'Monday to Friday',
                specialties: 'Heart conditions, blood pressure, cholesterol'
            },
            dermatologist: {
                doctors: ['Dr. Brown', 'Dr. Davis'],
                timings: '10:00 AM - 6:00 PM',
                availability: 'Tuesday to Saturday',
                specialties: 'Skin conditions, allergies, cosmetic procedures'
            },
            pediatrician: {
                doctors: ['Dr. Wilson', 'Dr. Miller'],
                timings: '8:00 AM - 4:00 PM',
                availability: 'Monday to Friday',
                specialties: 'Child health, vaccinations, growth development'
            },
            neurologist: {
                doctors: ['Dr. Garcia', 'Dr. Martinez'],
                timings: '11:00 AM - 7:00 PM',
                availability: 'Wednesday to Sunday',
                specialties: 'Brain disorders, nerve conditions, migraines'
            },
            general_physician: {
                doctors: ['Dr. Anderson', 'Dr. Thomas'],
                timings: '8:00 AM - 8:00 PM',
                availability: 'Daily',
                specialties: 'General health checkups, routine care, minor illnesses'
            }
        };

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const message = userInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            
            // Clear input
            userInput.value = '';

            // Generate AI response
            setTimeout(() => {
                const response = generateAIResponse(message);
                addMessage(response, 'bot');
            }, 1000);
        }

        function addMessage(message, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            
            if (sender === 'user') {
                messageDiv.innerHTML = `<strong>You:</strong> ${message}`;
            } else {
                messageDiv.innerHTML = `<strong>AI Assistant:</strong> ${message}`;
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function generateAIResponse(userMessage) {
            try {
                const message = userMessage.toLowerCase();
                
                // Check for greetings
                if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
                    return "Hello! I'm here to help you with medical consultations. What would you like to know about our doctors?";
                }
                
                // Check for doctor specializations
                if (message.includes('cardiologist') || message.includes('heart')) {
                    const cardio = doctorData.cardiologist;
                    return `We have excellent cardiologists: ${cardio.doctors.join(' and ')}. They specialize in ${cardio.specialties}. They are available ${cardio.availability} from ${cardio.timings}. Would you like to book an appointment?`;
                }
                
                if (message.includes('dermatologist') || message.includes('skin')) {
                    const derma = doctorData.dermatologist;
                    return `Our dermatologists ${derma.doctors.join(' and ')} specialize in ${derma.specialties}. They are available ${derma.availability} from ${derma.timings}. Would you like to book an appointment?`;
                }
                
                if (message.includes('pediatrician') || message.includes('child') || message.includes('kids')) {
                    const pedia = doctorData.pediatrician;
                    return `For children's healthcare, we have ${pedia.doctors.join(' and ')} who specialize in ${pedia.specialties}. They are available ${pedia.availability} from ${pedia.timings}. Would you like to book an appointment?`;
                }
                
                if (message.includes('neurologist') || message.includes('brain') || message.includes('nerve')) {
                    const neuro = doctorData.neurologist;
                    return `Our neurologists ${neuro.doctors.join(' and ')} specialize in ${neuro.specialties}. They are available ${neuro.availability} from ${neuro.timings} for brain and nervous system consultations. Would you like to book an appointment?`;
                }
                
                if (message.includes('general') || message.includes('physician') || message.includes('checkup')) {
                    const gp = doctorData.general_physician;
                    return `Our general physicians ${gp.doctors.join(' and ')} specialize in ${gp.specialties}. They are available ${gp.availability} from ${gp.timings}. Would you like to book an appointment?`;
                }
                
                // Check for timing queries
                if (message.includes('timing') || message.includes('time') || message.includes('schedule')) {
                    return "Our doctors have different schedules:\n• Cardiologists: 9:00 AM - 5:00 PM (Mon-Fri)\n• Dermatologists: 10:00 AM - 6:00 PM (Tue-Sat)\n• Pediatricians: 8:00 AM - 4:00 PM (Mon-Fri)\n• Neurologists: 11:00 AM - 7:00 PM (Wed-Sun)\n\nWhich specialist are you looking for?";
                }
                
                // Check for availability
                if (message.includes('available') || message.includes('appointment')) {
                    return "Most of our doctors have appointments available this week. To check specific availability and book an appointment, please visit our 'Find Doctors' page or let me know which specialist you need.";
                }
                
                // Check for location
                if (message.includes('location') || message.includes('address') || message.includes('where')) {
                    return "Our main clinic is located at MedConsult Health Center, 123 Medical Plaza, Healthcare District. We also have satellite clinics in various locations. Which area are you looking for?";
                }
                
                // Basic medical queries
                if (message.includes('fever') || message.includes('cold') || message.includes('cough')) {
                    return "For fever, cold, or cough symptoms, I recommend consulting with our general physicians or pediatricians (for children). If symptoms persist or worsen, please seek immediate medical attention. Would you like me to help you find an available doctor?";
                }
                
                if (message.includes('emergency') || message.includes('urgent')) {
                    return "For medical emergencies, please call 911 immediately or visit the nearest emergency room. For urgent but non-emergency cases, our doctors have same-day appointment slots available.";
                }
                
                // Default response
                return "I can help you with information about our doctors, their specializations, timings, and availability. You can also ask me basic medical questions. Could you please be more specific about what you're looking for?";
            } catch (error) {
                console.error('Error generating AI response:', error);
                return "I'm sorry, I encountered an error while processing your request. Please try again or ask a different question.";
            }
        }
    </script>
    <?php endif; ?>
</body>
</html>
