<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DocuVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            width: 100%;
            max-width: 900px;
            margin: 2rem auto;
        }

        .card {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: row;
            align-items: stretch;
            min-height: 400px;
        }

        .left-side {
            background-color: #ffffff;
            color: #333333;
            padding: clamp(1.5rem, 4vw, 2rem);
            width: 50%;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left-side h2 {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: bold;
        }

        .left-side p {
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .right-side {
            background-color: #4E2A84;
            color: white;
            padding: clamp(1.5rem, 4vw, 2rem);
            width: 50%;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .right-side h3 {
            font-size: clamp(1.25rem, 3.5vw, 1.75rem);
            margin-bottom: 0.5rem;
        }

        .right-side p {
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        }

        .form-label {
            font-weight: 500;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        }

        .form-control {
            border-radius: 8px;
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.3);
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        }

        .btn-login {
            background-color: #f2f2f2;
            color: black;
            border-color: black;
            border-radius: 8px;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            padding: 0.5rem;
        }

        .btn-login:hover {
            background-color: #4E2A84;
            color: white;
        }

        .btn-signup {
            background-color: #4E2A84;
            color: white;
            border-radius: 8px;
            border-color: white;
            margin-top: 0.5rem;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            padding: 0.5rem;
        }

        .btn-signup:hover {
            background-color: #f2f2f2;
            color: #333333;
        }

        .btn-back {
            background-color: rgb(250, 118, 118);
            color: white;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            padding: 0.5rem;
        }

        .btn-back:hover {
            background-color: white;
            color: black;
            border-color: #333333;
        }

        .error-message {
            color: red;
            font-size: clamp(0.75rem, 2vw, 0.85rem);
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .card {
                flex-direction: column;
                min-height: auto;
            }

            .left-side,
            .right-side {
                width: 100%;
                border-radius: 0;
            }

            .left-side {
                border-top-left-radius: 12px;
                border-top-right-radius: 12px;
            }

            .right-side {
                border-bottom-left-radius: 12px;
                border-bottom-right-radius: 12px;
            }

            .card-container {
                margin: 1rem auto;
                padding: 0.5rem;
            }
        }

        @media (max-width: 576px) {

            .left-side h2,
            .right-side h3 {
                font-size: clamp(1.25rem, 5vw, 1.5rem);
            }

            .form-control,
            .btn {
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            }

            .card-container {
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card card-container">
                    <!-- Left side: Register Form -->
                    <div class="left-side">
                        <h2>Create an Account</h2>
                        <p>Register to start using the platform.</p>
                        <form id="registerForm" method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div id="emailError" class="error-message"></div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                <div id="passwordError" class="error-message"></div>
                            </div>
                            <button type="submit" class="btn btn-login w-100 mb-3">Register</button>
                        </form>
                    </div>
                    <!-- Right side: Login Button -->
                    <div class="right-side">
                        <h3>Already have an account?</h3>
                        <p>Click below to log in</p>
                        <a href="{{ route('login') }}" class="btn btn-signup w-100">Login</a>
                        <a href="/" class="btn btn-back w-100">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle form submission and validation for password and email
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            // Reset error messages
            document.getElementById('passwordError').innerHTML = '';
            document.getElementById('emailError').innerHTML = '';

            // Validate passwords match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                event.preventDefault(); // Prevent form submission
                document.getElementById('passwordError').innerHTML = 'Passwords do not match.';
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
            }

            // Validate if email is already used (mock for client-side, replace with server-side check)
            const email = document.getElementById('email').value;
            const usedEmails = ['example@example.com']; // Example used email addresses
            if (usedEmails.includes(email)) {
                event.preventDefault(); // Prevent form submission
                document.getElementById('emailError').innerHTML = 'This email is already in use.';
                document.getElementById('email').value = '';
            }
        });
    </script>
</body>

</html>