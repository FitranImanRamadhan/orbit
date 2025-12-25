<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/img/logo/1.png" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <title>Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #5848e9, #8676ff);
        }

        .login-card {
            width: 380px;
            background: #ffffff;
            padding: 35px 40px;
            border-radius: 28px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.7s ease;
        }

        @keyframes fadeIn {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0px); opacity: 1; }
        }

        .login-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #2c2c2c;
        }

        .subtitle {
            font-size: 0.88rem;
            color: #666;
            margin-bottom: 25px;
        }

        .inputField {
            width: 100%;
            height: 45px;
            border-radius: 10px;
            border: 2px solid #d8d8e8;
            padding-left: 40px;
            font-weight: 500;
            transition: .25s;
        }

        .inputField:focus {
            border-color: #695cfe;
            box-shadow: 0 0 0 3px rgba(105, 92, 254, 0.15);
            outline: none;
        }

        .form-group {
            position: relative;
            margin-bottom: 18px;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #7b7b7b;
            font-size: 1.1rem;
        }

        #button {
            width: 100%;
            height: 45px;
            border-radius: 10px;
            border: none;
            background: #695cfe;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            transition: 0.25s;
        }

        #button:hover {
            background: #584be0;
            box-shadow: 0 6px 18px rgba(105, 92, 254, 0.4);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h3 class="login-title text-center">Selamat Datang</h3>
        <p class="subtitle text-center">Silakan masuk untuk melanjutkan</p>

        <form class="form_main" id="formLogin">
            <div class="form-group">
                <i class="bi bi-person"></i>
                <input placeholder="Username" name="username" id="username" class="inputField" type="text">
            </div>
            <div class="form-group">
                <i class="bi bi-lock"></i>
                <input placeholder="Password" name="password" id="password" class="inputField" type="password">
            </div>
            <button type="button" id="button" onclick="btnLogin()">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('.form_main').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnLogin();
            }
        });

        function btnLogin() {
            const username = $('#username').val().trim();
            const password = $('#password').val().trim();
            if (username === '' || password === '') {
                Swal.fire('Peringatan', 'Username dan password tidak boleh kosong!', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('login.post') }}",
                type: 'POST',
                dataType: 'json',
                data: { username: username, password: password },
                success: function(response) {
                    if (response.success) {
                       
                            window.location.href = response.redirect;
                        
                    } else {
                        Swal.fire('Login Gagal', response.message || 'Username atau password salah.', 'error');
                    }
                },
               error: function(xhr) {
                    const res = xhr.responseJSON;
                    const msg = res?.message || 'Terjadi kesalahan pada server';

                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    </script>

</body>
</html>
