{{-- resources/views/emails/layouts/base.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $asignatura ?? 'Pacientes Virtuales' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F5F7FA;
            color: #2C3E50;
            line-height: 1.6;
        }

        .email-wrapper {
            max-width: 680px;
            margin: 40px auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border: 2.5px solid rgba(91, 231, 196, 0.3);
            border-bottom: 4px solid rgba(91, 231, 196, 0.5);
        }

        /* HEADER */
        .email-header {
            background: linear-gradient(135deg, #5BE7C4 0%, #4FC1E9 100%);
            padding: 28px 40px;
            text-align: center;
        }

        .email-header-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.3px;
        }

        /* BODY */
        .email-body {
            padding: 40px 48px;
        }

        .email-greeting {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2C3E50;
            margin-bottom: 12px;
        }

        .email-intro {
            font-size: 0.95rem;
            color: #4A5568;
            margin-bottom: 28px;
        }

        /* INFO CARD */
        .email-info-card {
            background: #F8FAFC;
            border: 1.5px solid #E8ECF0;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }

        .email-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #E8ECF0;
            font-size: 0.9rem;
        }

        .email-info-row:last-child {
            border-bottom: none;
        }

        .email-info-label {
            font-weight: 600;
            color: #2C3E50;
            min-width: 110px;
        }

        .email-info-value {
            color: #4A5568;
        }

        /* BUTTON */
        .email-btn-wrapper {
            text-align: center;
            margin-bottom: 28px;
        }

        .email-btn {
            display: inline-block;
            background: linear-gradient(135deg, #5BE7C4 0%, #4FC1E9 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* NOTE */
        .email-note {
            background: #fffbeb;
            border-left: 4px solid #F39C12;
            border-radius: 0 8px 8px 0;
            padding: 14px 18px;
            font-size: 0.85rem;
            color: #92400e;
            margin-bottom: 28px;
        }

        /* FOOTER */
        .email-footer {
            background: #F8FAFC;
            border-top: 1px solid #E8ECF0;
            padding: 24px 40px;
            text-align: center;
        }

        .email-footer-text {
            font-size: 0.8rem;
            color: #7F8C9A;
            line-height: 1.7;
        }

        .email-footer-text a {
            color: #5BE7C4;
            text-decoration: none;
        }

        .email-divider {
            height: 1px;
            background: #E8ECF0;
            margin: 24px 0;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">

        {{-- HEADER --}}

        <div class="email-header">
            <div class="email-header-title">Pacientes Virtuales</div>
        </div>

        {{-- CONTENIDO --}}
        <div class="email-body">
            {{ $slot }}
        </div>

        {{-- FOOTER --}}
        <div class="email-footer">
            <div class="email-footer-text">
                Este email fue enviado automáticamente por <strong>Pacientes Virtuales</strong>.<br>
                Si crees que has recibido este email por error, puedes ignorarlo.<br><br>
                © {{ date('Y') }} Pacientes Virtuales · Todos los derechos reservados.
            </div>
        </div>

    </div>
</body>

</html>