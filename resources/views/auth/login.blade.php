@extends('welcome')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <form id="loginForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback" id="passwordError"></div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100" id="loginBtn">
                            <span id="btnText">Login</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    const token = localStorage.getItem('auth_token');
    if (token) {
        fetch('{{ url('/api/user') }}', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                console.log('Token válido, redirigiendo al dashboard...');
                window.location.replace('{{ url('/dashboard') }}');
            } else {
                console.log('Token inválido, limpiando localStorage...');
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
            }
        })
        .catch(error => {
            console.error('Error verificando token:', error);
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        });
    }

    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const alertContainer = document.getElementById('alertContainer');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        emailInput.classList.remove('is-invalid');
        passwordInput.classList.remove('is-invalid');
        alertContainer.innerHTML = '';
        
        btn.disabled = true;
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        
        try {
            const response = await fetch('{{ url('/api/login') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: emailInput.value,
                    password: passwordInput.value
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                console.log('Login exitoso, guardando token...');
                localStorage.setItem('auth_token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                alertContainer.innerHTML = '<div class="alert alert-success">Login exitoso! Redirigiendo...</div>';
                
                setTimeout(() => {
                    window.location.replace('{{ url('/dashboard') }}');
                }, 500);
                
            } else {

                if (data.errors) {
                    if (data.errors.email) {
                        emailInput.classList.add('is-invalid');
                        document.getElementById('emailError').textContent = data.errors.email[0];
                    }
                    if (data.errors.password) {
                        passwordInput.classList.add('is-invalid');
                        document.getElementById('passwordError').textContent = data.errors.password[0];
                    }
                    if (data.errors.authentication) {
                        alertContainer.innerHTML = `<div class="alert alert-danger">${data.errors.authentication[0]}</div>`;
                    }
                } else {
                    alertContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Error al iniciar sesión'}</div>`;
                }
                

                btn.disabled = false;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = '<div class="alert alert-danger">Error de conexión. Por favor, intenta nuevamente.</div>';
            

            btn.disabled = false;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        }
    });
})();
</script>
@endsection
