@extends('layouts.app')

@section('content')
<style>
    .profile-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
        background: linear-gradient(135deg, #007bff, #0057b8);
    }

    .form-control, .form-control:focus {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
    }

    .btn-primary {
        background: #007bff;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-primary:hover {
        background: #0057b8;
        transform: translateY(-2px);
    }

    .btn-secondary {
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
    }

    .profile-img {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 50%;
        border: 3px solid #e9ecef;
        transition: border-color 0.3s ease;
    }

    .profile-img:hover {
        border-color: #007bff;
    }

    .alert-success {
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 500;
        color: #343a40;
    }

    @media (max-width: 576px) {
        .profile-card {
            margin: 0 1rem;
        }

        .card-header {
            padding: 1rem;
        }

        .btn-primary, .btn-secondary {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .profile-img {
            width: 100px;
            height: 100px;
        }
    }
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card profile-card">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Edit Your Profile</h4>
                </div>
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">New Password (Optional)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="mb-4">
                            <label for="photoprofile" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control @error('photoprofile') is-invalid @enderror" id="photoprofile" name="photoprofile" accept="image/*">
                            @error('photoprofile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remove_photo" name="remove_photo">
                            <label class="form-check-label" for="remove_photo">Remove Profile Photo</label>
                        </div>

                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('profile.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <!-- Display Profile Image -->
                    <div class="profile-img mt-4">
                        @if ($user->photoprofile)
                            <img src="{{ asset('storage/' . $user->photoprofile) }}" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                <i class="bi bi-person" style="font-size: 3rem; color: #6c757d;"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection