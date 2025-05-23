<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuVault - Document Management Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
        }

        .hero {
            background: linear-gradient(90deg, #0d6efd, #0a58ca);
            min-height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
        }

        .hero p {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
        }

        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-out;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .navbar {
            padding: 1rem;
        }

        .navbar-brand {
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        .nav-link {
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .section-padding {
            padding: 3rem 1rem;
        }

        .card {
            height: 100%;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: clamp(1.25rem, 3vw, 1.5rem);
        }

        .card-text {
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        .feature-icon {
            font-size: clamp(2.5rem, 6vw, 3rem);
        }

        .footer {
            padding: 2rem 1rem;
        }

        @media (max-width: 768px) {
            .navbar-nav {
                text-align: center;
            }

            .navbar-nav .nav-item {
                margin: 0.5rem 0;
            }

            .hero {
                min-height: 40vh;
            }

            .hero h1 {
                font-size: clamp(1.5rem, 6vw, 2.5rem);
            }

            .hero p {
                font-size: clamp(0.9rem, 3vw, 1.1rem);
            }

            .card {
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .section-padding {
                padding: 2rem 0.5rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="navbar navbar-expand-lg navbar-light container">
            <a class="navbar-brand fw-bold" href="#">DocuVault</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="btn btn-primary text-white px-4" href="/login">Get Started</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero text-white">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4 fade-in">Streamline Your Document Management</h1>
            <p class="lead mb-4 fade-in">Securely store, organize, and collaborate on your documents with DocuVault.</p>
            @if (Route::has('login'))
            <div class="mt-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-custom btn-primary">Go to Dashboard</a>
                @else
                <a href="{{ route('login') }}" class="btn btn-custom btn-secondary">Login</a>
                <a href="{{ route('register') }}" class="btn btn-custom btn-primary">Register</a>
                @endauth
            </div>
            @endif
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section-padding">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Why Choose DocuVault?</h2>
            <div class="row">
                <div class="col-12 col-md-4 text-center fade-in mb-4 mb-md-0">
                    <div class="feature-icon mb-3">🔒</div>
                    <h3 class="h5 fw-bold mb-3">Top-Notch Security</h3>
                    <p>End-to-end encryption and access controls keep your documents safe.</p>
                </div>
                <div class="col-12 col-md-4 text-center fade-in mb-4 mb-md-0">
                    <div class="feature-icon mb-3">📁</div>
                    <h3 class="h5 fw-bold mb-3">Smart Organization</h3>
                    <p>Easily categorize, tag, and search your documents with our intuitive system.</p>
                </div>
                <div class="col-12 col-md-4 text-center fade-in mb-4 mb-md-0">
                    <div class="feature-icon mb-3">🤝</div>
                    <h3 class="h5 fw-bold mb-3">Seamless Collaboration</h3>
                    <p>Share and collaborate in real-time with your team, anywhere, anytime.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white footer">
        <div class="container text-center">
            <p>© 2025 DocuVault. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fade-in animation on scroll
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.fade-in');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });
            elements.forEach(element => observer.observe(element));
        });
    </script>
</body>

</html>