<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | PosPerú Enterprise</title>
    <!-- Vinculación del CSS externo -->
    <link rel="stylesheet" href="view/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        <!-- Panel Izquierdo (Corporativo) -->
        <div class="login-left">
            <div class="left-header">
                <div class="brand">
                    <div class="logo-icon">
                        <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h2>PosPerú <span class="badge-pro">PRO</span></h2>
                        <p>Enterprise IT Ops Suite</p>
                    </div>
                </div>
                <div class="status-badge">
                    <div class="status-dot"></div> Sistemas 100% Operativos
                </div>
            </div>

            <div class="left-content">
                <h1>Orquestación ágil de infraestructura & operaciones TI</h1>
                <p>Control unificado, automatización continua y observabilidad integral para equipos de tecnología de alto rendimiento.</p>
            </div>

            <div class="left-footer">
                <div style="display: flex; gap: 20px;">
                    <span>✓ Certificación ISO/IEC 27001</span>
                    <span>✓ SOC 2 Tipo II Verificado</span>
                </div>
                <div>v1.0.0-enterprise</div>
            </div>
        </div>

        <!-- Panel Derecho (Formulario) -->
        <div class="login-right">
            <div class="login-form-container">
                <h2>Iniciar Sesión</h2>
                <p>Accede a la consola de gestión de tareas corporativas con tus credenciales asignadas.</p>

                <!-- Alerta de Error conectada a JS -->
                <div id="mensaje-error" class="alert-error"></div>

                <form id="loginForm">
                    <!-- Campo Correo -->
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="correo">Correo Corporativo</label>
                        </div>
                        <div class="input-wrapper">
                            <!-- Icono de sobre -->
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                            </svg>
                            <input type="email" id="correo" name="correo" placeholder="nombre@posperu.com" required>
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="password">Contraseña</label>
                            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="input-wrapper">
                            <!-- Icono de candado -->
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                            <!-- Icono de ojo -->
                            <svg class="toggle-password" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" onclick="togglePassword()">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Botón Submit -->
                    <button type="submit" id="btnSubmit" class="btn-submit">
                        Iniciar Sesión <span>&rarr;</span>
                    </button>
                    <div class="divider">
                        <span>o</span>
                    </div>

                    <!-- Botón Secundario: Crear Cuenta / Registrarse -->
                    <a href="index.php?action=register" class="btn-register">
                        ¿No tienes una cuenta? Regístrate aquí
                    </a>
                </form>
                </form>

                <div class="right-footer">
                    ¿Aún no tienes acceso corporativo? <a href="#">Contactar a IT / Soporte</a>
                    <p>Protegido por políticas de acceso biométrico Zero-Trust y cifrado AES-256.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Script de AJAX que ya teníamos -->
    <script src="view/javascript/login.js?v=2"></script>
    
    <!-- Script pequeño para el ojito de la contraseña -->
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }
    </script>
</body>
</html>