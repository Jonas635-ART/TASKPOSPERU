<!-- view/recuperar.php -->
<?php if (!defined('APP_INIT')) { http_response_code(403); exit(); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | PosPerú Enterprise</title>
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
                <h1>Recuperación asistida por Soporte TI</h1>
                <p>Solicita la reactivación de tu acceso y el Administrador del sistema generará un código temporal de un solo uso para que restablezcas tu contraseña.</p>
            </div>

            <div class="left-footer">
                <div style="display: flex; gap: 20px;">
                    <span>✓ Autorización Corporativa</span>
                    <span>✓ Código Temporal de 1 Uso</span>
                </div>
                <div>v1.0.0-enterprise</div>
            </div>
        </div>

        <!-- Panel Derecho -->
        <div class="login-right">
            <div class="login-form-container">
                <h2>Recuperar Contraseña</h2>
                <p>Solicita un código temporal al Administrador o ingrésalo si ya te lo proporcionaron.</p>

                <!-- Pestañas -->
                <div class="reset-tabs" role="tablist">
                    <button type="button" id="tabSolicitar" class="reset-tab active" role="tab" aria-selected="true">1. Solicitar código</button>
                    <button type="button" id="tabCanjear" class="reset-tab" role="tab" aria-selected="false">2. Ya tengo código</button>
                </div>

                <!-- Alerta dinámica de JS -->
                <div id="mensaje-alerta" class="alert-error"></div>

                <!-- FORMULARIO 1: Solicitar al Administrador -->
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
                        Enviar Solicitud al Admin <span>&rarr;</span>
                    </button>
                </form>

                <!-- FORMULARIO 2: Canjear código temporal -->
                <form id="formCanjear" style="display: none;">
                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="correo_canje">Correo Electrónico</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                            </svg>
                            <input type="email" id="correo_canje" name="correo" placeholder="nombre@posperu.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-header">
                            <label class="form-label" for="codigo_temporal" style="color: #4f46e5;">Código Temporal del Admin</label>
                        </div>
                        <div class="input-wrapper">
                            <svg fill="none" stroke="#4f46e5" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                            <input type="text" id="codigo_temporal" name="codigo_temporal" placeholder="Ej: POS-4829-K7" style="text-transform: uppercase; font-family: monospace; font-weight: 700;" required>
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

