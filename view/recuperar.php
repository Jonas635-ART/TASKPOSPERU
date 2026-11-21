<!-- view/recuperar.php -->
<?php if (!defined('APP_INIT')) { http_response_code(403); exit(); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | PosPerú Enterprise</title>
    <link rel="icon" type="image/jpeg" href="view/img/logo.jpg">
    <link rel="stylesheet" href="view/css/login.css">
    <style>
        .reset-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .reset-tab {
            border: none;
            background: transparent;
            padding: 9px 10px;
            border-radius: 7px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }
        .reset-tab.active {
            background: #ffffff;
            color: #4f46e5;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <!-- Panel Izquierdo Corporativo -->
        <div class="login-left">
            <div class="left-header">
                <div class="brand">
                    <div class="logo-icon">
                        <img src="view/img/logo.jpg" alt="PosPerú" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div class="brand-text">
                        <h2>PosPerú <span class="badge-pro">PRO</span></h2>
                    </div>
                </div>

            </div>

            <div class="left-content">
                <h1>Recuperación  por correo</h1>
                <p>Ingresa tu correo registrado para recibir un código de verificación de 6 dígitos y restablecer tu contraseña.</p>
            </div>

            <div class="left-footer">
                <div style="display: flex; gap: 20px;">
                    <span>✓ Envío Automático 24/7</span>
                    <span>✓ Código de 6 Dígitos</span>
                </div>
                <div>v1.0.0-enterprise</div>
            </div>
        </div>

        <!-- Panel Derecho -->
        <div class="login-right">
            <div class="login-form-container">
                <h2>Recuperar Contraseña</h2>
                <p>Te enviaremos un código de 6 dígitos a tu correo para validar tu identidad.</p>

                <!-- Pestañas -->
                <div class="reset-tabs" role="tablist">
                    <button type="button" id="tabSolicitar" class="reset-tab active" role="tab" aria-selected="true">1. Enviar código</button>
                    <button type="button" id="tabCanjear" class="reset-tab" role="tab" aria-selected="false">2. Ya tengo código</button>
                </div>

                <!-- Alerta dinámica de JS -->
                <div id="mensaje-alerta" class="alert-error"></div>

                <!-- FORMULARIO 1: Enviar código por correo -->
                <form id="formSolicitar">
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="correo_solicitud">Tu Correo Registrado</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                            </svg>
                            <input type="email" id="correo_solicitud" name="correo" placeholder="nombre@posperu.com" required>
                        </div>
                    </div>

                    <button type="submit" id="btnSolicitar" class="btn-submit">
                        Enviar Código a mi Correo <span>&rarr;</span>
                    </button>
                </form>

                <!-- FORMULARIO 2: Canjear código de 6 dígitos -->
                <form id="formCanjear" style="display: none;">
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="correo_canje">Correo Electrónico</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                            </svg>
                            <input type="email" id="correo_canje" name="correo" placeholder="nombre@gmail.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="codigo_temporal" style="color: #4f46e5;">Código de Verificación (6 dígitos)</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="#4f46e5" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                            <input type="text" id="codigo_temporal" name="codigo_temporal" inputmode="numeric" maxlength="6" placeholder="Ej: 482910" style="font-family: monospace; font-weight: 700; letter-spacing: 3px;" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="password">Nueva Contraseña</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="confirm_password">Confirmar Nueva Contraseña</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                            </svg>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu nueva contraseña" required>
                        </div>
                    </div>

                    <button type="submit" id="btnCanjear" class="btn-submit">
                        Actualizar Contraseña <span>&rarr;</span>
                    </button>
                </form>

                <div class="divider">
                    <span>o</span>
                </div>

                <a href="index.php?action=login" class="btn-register">
                    Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>

    <script src="view/javascript/recuperar.js"></script>
</body>
</html>

