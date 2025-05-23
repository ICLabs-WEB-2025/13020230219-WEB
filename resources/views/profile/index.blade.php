@extends('layouts.app')

@section('content')
<div class="container mt-4 mt-md-5">
    <h3 class="fw-bold mb-4 text-dark">Your Profile</h3>
    <div class="row justify-content-center">
        <!-- Profile Image Section -->
        <div class="col-12 col-md-4 text-center mb-4 mb-md-0">
            <div class="card border-0">
                <div class="card-body" style="height: 200px;">
                    <div class="profile-img mx-auto mb-3">
                        @if ($user->photoprofile)
                        <img src="{{ asset('storage/' . $user->photoprofile) }}" alt="Profile Picture" class="rounded-circle img-fluid" style="max-width: 150px; height: auto; ">
                        @else
                        <div class="d-flex align-items-center justify-content-center h-10 ">
                            <svg class="rounded-circle img-fluid" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="50" fill="#e9ecef" />
                                <path d="M50 25C42.625 25 36.5 31.125 36.5 38.5C36.5 45.875 42.625 52 50 52C57.375 52 63.5 45.875 63.5 38.5C63.5 31.125 57.375 25 50 25ZM50 62C41.875 62 26 66.0625 26 74.25V81.5H74V74.25C74 66.0625 58.125 62 50 62Z" fill="#6c757d" />
                            </svg>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details Section -->
        <div class="col-12 col-md-8">
            <div class="card shadow-sm p-3 p-md-4 mb-4" style="border-radius: 10px; background: #fff; height: 200px; ">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex flex-column">
                            <label for="username" class="form-label text-muted fw-bold mb-1">Username</label>
                            <p class="form-control bg-light p-2 rounded border" style="transition: all 0.3s;">{{ $user->username }}</p>
                        </div>
                        <div class="d-flex flex-column mt-1">
                            <label for="email" class="form-label text-muted fw-bold mb-1">Email</label>
                            <p class="form-control bg-light p-2 rounded border" style="transition: all 0.3s;">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Button -->
            <a href="{{ route('profile.update') }}" class="btn btn-dark w-100 w-md-auto mt-3 px-4 py-2">Ubah Profile</a>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Card hover effect */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    /* Form control hover effect */
    .form-control {
        transition: all 0.3s ease;
    }

    .form-control:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }

    /* Profile image styling */
    .profile-img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #f0f0f0;
        margin: 0 auto;
    }

    .profile-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Button styling */
    .btn-dark {
        background-color: #3e3e3e;
        color: #fff;
        font-weight: bold;
        border-radius: 0.5rem;
        text-transform: uppercase;
    }

    .btn-dark:hover {
        background-color: #2d2d2d;
    }

    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .profile-img {
            width: 120px;
            height: 120px;
        }

        .profile-img i {
            font-size: 60px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-dark {
            padding: 8px 16px;
        }
    }

    @media (max-width: 576px) {
        .container {
            padding: 0 15px;
        }

        .card {
            padding: 1rem;
        }

        .profile-img {
            width: 100px;
            height: 100px;
        }

        .profile-img i {
            font-size: 50px;
        }

        .form-control {
            font-size: 0.9rem;
        }

        .btn-dark {
            font-size: 0.9rem;
        }
    }
</style>
@endsection