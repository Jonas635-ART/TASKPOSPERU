<!-- view/register.php -->
<?php if (!defined('APP_INIT')) { http_response_code(403); exit(); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | PosPerú Enterprise</title>
    <!-- Reutilizamos los mismos estilos del login -->
    <link rel="stylesheet" href="view/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        <!-- Panel Izquierdo Corporativo -->
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
                <h1>Únete a la plataforma centralizada de TI</h1>
                <p>Gestiona tickets de soporte, da seguimiento a tus requerimientos en tiempo real y colabora con el equipo de infraestructura.</p>
            </div>

            <div class="left-footer">
                <div style="display: flex; gap: 20px;">
                    <span>✓ Certificación ISO/IEC 27001</span>
                    <span>✓ PostgreSQL 16 Seguro</span>
                </div>
                <div>v1.0.0-enterprise</div>
            </div>
        </div>

        <!-- Panel Derecho: Formulario -->
        <div class="login-right">
            <div class="login-form-container">
                <h2>Crear Cuenta</h2>
                <p>Completa tus datos para registrarte en el sistema.</p>

                <!-- Alerta dinámica de JS -->
                <div id="mensaje-alerta" class="alert-error"></div>

                <form id="registerForm">
                    <!-- Nombre Completo -->
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="nombre">Nombre Completo</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                            </svg>
                            <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required>
                        </div>
                    </div>

                    <!-- Correo Corporativo -->
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="correo">Correo Electrónico</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                            </svg>
                            <input type="email" id="correo" name="correo" placeholder="nombre@posperu.com" required>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="password">Contraseña</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="confirm_password">Confirmar Contraseña</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                            </svg>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
                        </div>
                    </div>

                    <!-- Casilla para mostrar campo de código especial -->
                    <div class="checkbox-group" style="margin-bottom: 16px;">
                        <input type="checkbox" id="checkRolEspecial" onchange="toggleCodigoAcceso()">
                        <label for="checkRolEspecial" style="font-size: 0.8rem; color: #6366f1; font-weight: 600;">¿Cuentas con un código de equipo TI (Dev / Admin)?</label>
                    </div>

                    <!-- Campo de Código de Autorización (Oculto por defecto) -->
                    <div class="form-group" id="grupoCodigo" style="display: none;">
                        <div class="form-header">
                            <label class="form-label" for="codigo_acceso" style="color: #6366f1;">Código de Autorización Interna</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="#6366f1" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <input type="text" id="codigo_acceso" name="codigo_acceso" style="border-color: #c7d2fe; text-transform: uppercase;">
                        </div>
                    </div>

                    <!-- Botón Enviar -->
                    <button type="submit" id="btnSubmit" class="btn-submit">
                        Registrarme <span>&rarr;</span>
                    </button>

                    <div class="divider">
                        <span>o</span>
                    </div>

                    <!-- Botón Volver al Login -->
                    <a href="index.php?action=login" class="btn-register">
                        ¿Ya tienes cuenta? Inicia sesión
                    </a>
                </form>

            </div>
        </div>
    </div>

    <!-- Funcionalidad para ocultar/mostrar campo código de acceso -->
    <script>
        function toggleCodigoAcceso() {
            const check = document.getElementById('checkRolEspecial');
            const grupo = document.getElementById('grupoCodigo');
            const inputCodigo = document.getElementById('codigo_acceso');

            if (check.checked) {
                grupo.style.display = 'block';
                inputCodigo.focus();
            } else {
                grupo.style.display = 'none';
                inputCodigo.value = '';
            }
        }
    </script>

    <!-- Archivo JS con la petición fetch -->
    <script src="view/javascript/register.js"></script>
</body>
</html>