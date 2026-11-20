<?php 
    ob_start();
    session_start();
    require 'vendor/autoload.php';
    require_once 'config.php';
    require_once 'functions.php';
    require_once TEMPLATE.'head.php'; 

    $tokenParam = $_GET['Id'] ?? '';
    $quoteUuid = $tokenParam;
    $ahora = date("Y-m-d H:i:s");

    // Fetch accounts and configurations
    $api_url = URL_API."quote_account";
    $account = json_decode(API($jwt, $api_url, '', 'GET'), true);

    if (($account['Pais'] ?? '') == 'USA') {
        $account['Pais'] = 'US';
    } else {
        $account['Pais'] = 'MX';
    }

    $api_url = URL_API."PAYPAL";
    $paypal_account = json_decode(API($jwt, $api_url, '', 'GET'), true);

    $api_url = URL_API."SQUARE";
    $square_account = json_decode(API($jwt, $api_url, '', 'GET'), true);

    $api_url = URL_API."OPAY";
    $opay_account = json_decode(API($jwt, $api_url, '', 'GET'), true);

    $PayPlatform = $account['Pay_platform'] ?? 'SQUARE';
    $pay_account = ($PayPlatform == 'OPAY') ? $opay_account : $square_account;

    $card_charge_enabled = ($paypal_account['extracharge'] ?? null) == 1
        || ($square_account['extracharge'] ?? null) == 1
        || ($opay_account['extracharge'] ?? null) == 1;

    $card_charge_percentage = $card_charge_enabled && isset($pay_account['percentage'])
        ? (float) $pay_account['percentage']
        : 0;

    $extra_pctj = isset($pay_account['extracharge']) && $pay_account['extracharge'] == 1 && isset($pay_account['percentage'])
        ? (float) $pay_account['percentage'] / 100
        : 0;

    $paypal_extra_pctj = isset($paypal_account['extracharge']) && $paypal_account['extracharge'] == 1 && isset($paypal_account['percentage'])
        ? (float) $paypal_account['percentage'] / 100
        : 0;
?>
    <script type="text/javascript" src="https://openpay.s3.amazonaws.com/openpay.v1.min.js"></script>
    <script type="text/javascript" src="https://openpay.s3.amazonaws.com/openpay-data.v1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <?php if ($PayPlatform != 'OPAY'): ?>
        <?php if (($square_account['sandbox'] ?? '0') == '1'): ?>
            <script type="text/javascript" src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
        <?php else: ?>
            <script type="text/javascript" src="https://web.squarecdn.com/v1/square.js"></script>
        <?php endif; ?>
    <?php endif; ?>

    <link rel="stylesheet" href="css/general.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        /* Contenedor del flujo interactivo (lado izquierdo) */
        .workflow-column {
            position: relative;
        }

        @media (min-width: 992px) {
            .sticky-workflow-card {
                position: sticky;
                top: 80px;
                max-height: calc(100vh - 100px);
                overflow-y: auto;
                padding-right: 4px;
            }
            .sticky-workflow-card::-webkit-scrollbar {
                width: 6px;
            }
            .sticky-workflow-card::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
        }

        .card-workflow {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        /* Stepper de progreso */
        .stepper-container {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 12px 14px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: #94a3b8;
            transition: all 0.3s ease;
            flex: 0 0 auto;
            min-width: 60px;
        }
        .step-item span {
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 6px;
            white-space: nowrap;
            line-height: 1.2;
        }
        .step-item.active {
            color: #0d6efd;
        }
        .step-item.completed {
            color: #198754;
        }
        .step-num {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .step-item.active .step-num {
            background: #0d6efd;
            color: #ffffff;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }
        .step-item.completed .step-num {
            background: #198754;
            color: #ffffff;
        }
        .step-divider {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 14px 4px 0;
            min-width: 15px;
            transition: all 0.3s ease;
        }
        .step-divider.completed {
            background: #198754;
        }

        @media (max-width: 576px) {
            .stepper-container {
                padding: 10px 8px 8px;
            }
            .step-item {
                min-width: 50px;
            }
            .step-item span {
                font-size: 0.7rem;
                margin-top: 4px;
            }
            .step-num {
                width: 26px;
                height: 26px;
                font-size: 0.75rem;
            }
            .step-divider {
                margin: 12px 2px 0;
                min-width: 10px;
            }
            .contract-paper {
                padding: 20px 14px;
            }
            .card-workflow {
                padding: 1.2rem !important;
            }
        }

        /* Estilo Hoja de Contrato (lado derecho) */
        .contract-paper {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            line-height: 1.7;
            font-size: 1.05rem;
            min-height: 700px;
        }

        .contract-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        /* Canvas de Firma */
        .canvas-container {
            border: 2px dashed #0d6efd;
            border-radius: 12px;
            background: #fafafa;
            height: 220px;
            touch-action: none;
            position: relative;
        }
        .canvas-container canvas {
            width: 100%;
            height: 100%;
            cursor: crosshair;
            display: block;
        }

        /* Spinner Loader */
        .loader-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 260px;
            width: 100%;
        }
        .loader {
            border: 8px solid #f1f5f9;
            border-top: 8px solid #0d6efd;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Botones de acción */
        .btn-pay {
            background: #000000;
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-pay:hover {
            background: #222222;
            color: #ffffff;
        }
        .btn-pay:disabled {
            background: #cbd5e1;
        }

        /* Scroll indicator */
        #scroll-indicator {
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 1000;
            background: rgba(13, 110, 253, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            font-size: 0.85rem;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }
    </style>
</head>
<body>

<?php 
    require_once TEMPLATE.'nav.php';

    // Cargar traducciones del programa newcontract
    $lang = $_SESSION['Idioma'] ?? LANG;
    $api_url = URL_API."Traducciones_web";

    $data = json_encode(['program' => "newcontract"]);
    $TraduccionesNewContract = json_decode(API($jwt, $api_url, $data, 'GET'), true);

    // Asignación de compatibilidad global
    $Traducciones = $TraduccionesNewContract;

    // Función de traducción para el programa newcontract
    function TrdNC($id, $default = '') {
        global $TraduccionesNewContract;
        return (!empty($TraduccionesNewContract[$id])) ? $TraduccionesNewContract[$id] : $default;
    }
?>

<div class="container-fluid px-lg-4 py-4">

<?php
    // Validar token presente
    if (empty($tokenParam)) {
        echo '
        <div class="container py-5">
            <div class="alert alert-danger text-center p-5 rounded-4 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill fs-1 text-danger d-block mb-3"></i>
                <h3 class="fw-bold">'.TrdNC(1, 'Token no válido').'</h3>
                <p class="text-muted">'.TrdNC(2, 'No se ha especificado un identificador de cotización válido.').'</p>
                <a href="'.URL_BASE.'" class="btn btn-outline-dark mt-3">'.TrdNC(3, 'Volver al Inicio').'</a>
            </div>
        </div>';
        require_once TEMPLATE.'social.php';
        require_once TEMPLATE.'cart.php';
        require_once TEMPLATE.'scripts.php';
        require_once 'scripts.php';
        echo '</body></html>';
        exit();
    }

    $token = $tokenParam;

    // Consultar cotización
    $api_url = URL_API."quotes";
    $data = json_encode(['token' => $token]);
    $cotizacion = json_decode(API($jwt, $api_url, $data, 'GET'), true);

    if ($cotizacion) {
        if ($ahora > $cotizacion['ExpDate']) {
            echo '
            <div class="container py-5">
                <div class="alert alert-warning text-center p-5 rounded-4 shadow-sm">
                    <i class="bi bi-clock-history fs-1 text-warning d-block mb-3"></i>
                    <h3 class="fw-bold">'.TrdNC(4, 'Cotización expirada').'</h3>
                    <p class="text-muted">'.TrdNC(5, 'Fecha límite:').' '.$cotizacion['ExpDate'].'</p>
                    <a href="'.URL_BASE.'" class="btn btn-outline-dark mt-3">'.TrdNC(3, 'Volver al Inicio').'</a>
                </div>
            </div>';
            require_once TEMPLATE.'social.php';
            require_once TEMPLATE.'cart.php';
            require_once TEMPLATE.'scripts.php';
            require_once 'scripts.php';
            echo '</body></html>';
            exit();
        }
    } else {
        echo '
        <div class="container py-5">
            <div class="alert alert-danger text-center p-5 rounded-4 shadow-sm">
                <i class="bi bi-x-circle-fill fs-1 text-danger d-block mb-3"></i>
                <h3 class="fw-bold">'.TrdNC(6, 'Cotización no encontrada').'</h3>
                <a href="'.URL_BASE.'" class="btn btn-outline-dark mt-3">'.TrdNC(3, 'Volver al Inicio').'</a>
            </div>
        </div>';
        require_once TEMPLATE.'social.php';
        require_once TEMPLATE.'cart.php';
        require_once TEMPLATE.'scripts.php';
        require_once 'scripts.php';
        echo '</body></html>';
        exit();
    }

    // Consultar datos completos del Lead
    $api_url = URL_API."quote_data";
    $data = json_encode(['lead' => $cotizacion['IdQuote']]);        
    $respuesta = json_decode(API($jwt, $api_url, $data, 'GET'), true);

    $lead = $respuesta['lead'];
    $lead_details = $respuesta['lead_details'];
    $customer = $respuesta['customer'];        
    $organization = $respuesta['organization'];
    $venue = $respuesta['venue'];
    $discounts = $respuesta['discounts'];
    $gifcrds = $respuesta['gifcrds'];

    if (($lead['Customer'] ?? 0) > 0) {
        $Nom = $customer['Nombres'] ?? '';
        $Ape = $customer['Apellidos'] ?? '';
        $Correo = $customer['Correo'] ?? '';
    } else {
        $Nom = $organization['Nombre'] ?? '';
        $Ape = '';
        $Correo = $organization['Correo'] ?? '';
    }

    $isAlreadySigned = !empty($cotizacion['Contrato']);
    $initialTId = $_GET['TId'] ?? '';
    $isAlreadyPaid = ($lead['Total'] <= 0) || !empty($initialTId) || isset($_GET['payment_success']);

    if ($lead['Total'] <= 0 && !$isAlreadyPaid) {
        $api_url = URL_API."gifcard_pay";
        $data = json_encode(['token' => $token]);            
        $resGC = json_decode(API($jwt, $api_url, $data, 'POST'), true);
        $isAlreadyPaid = true;
        $initialTId = 'GC-100';
    }

    $pdfNombre = $cotizacion['UUID'].".pdf";
    $pdfDataUri = !empty($cotizacion['Contrato']) ? ("data:application/pdf;base64," . $cotizacion['Contrato']) : ($token.".pdf");

    ob_end_flush();
?>

    <div class="row g-4">
        <!-- COLUMNA IZQUIERDA: FLUJO INTERACTIVO (1/3 en pantalla grande) -->
        <div class="col-12 col-lg-4 col-xl-4 workflow-column">
            <div class="sticky-workflow-card">
                <div class="card card-workflow p-4">
                    
                    <!-- Stepper de Progreso -->
                    <div class="stepper-container">
                        <div class="step-item <?= $isAlreadyPaid ? 'completed' : 'active' ?>" id="step-item-1">
                            <div class="step-num" id="step-num-1">
                                <?= $isAlreadyPaid ? '<i class="bi bi-check-lg"></i>' : '1' ?>
                            </div>
                            <span><?= TrdNC(7, 'Pago') ?></span>
                        </div>
                        <div class="step-divider <?= $isAlreadyPaid ? 'completed' : '' ?>" id="step-divider-1"></div>
                        
                        <div class="step-item <?= $isAlreadySigned ? 'completed' : ($isAlreadyPaid ? 'active' : '') ?>" id="step-item-2">
                            <div class="step-num" id="step-num-2">
                                <?= $isAlreadySigned ? '<i class="bi bi-check-lg"></i>' : '2' ?>
                            </div>
                            <span><?= TrdNC(8, 'Firma') ?></span>
                        </div>
                        <div class="step-divider <?= $isAlreadySigned ? 'completed' : '' ?>" id="step-divider-2"></div>
                        
                        <div class="step-item <?= $isAlreadySigned ? 'active completed' : '' ?>" id="step-item-3">
                            <div class="step-num" id="step-num-3">
                                <?= $isAlreadySigned ? '<i class="bi bi-check-lg"></i>' : '3' ?>
                            </div>
                            <span><?= TrdNC(9, 'Descarga') ?></span>
                        </div>
                    </div>

                    <!-- PASO 1: SECCIÓN DE PAGO (MAKEPAYMENT) -->
                    <div id="step-payment" style="<?= ($isAlreadyPaid || $isAlreadySigned) ? 'display: none;' : '' ?>">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-credit-card-2-front-fill text-primary fs-3 me-2"></i>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark"><?= TrdNC(10, 'Realizar Pago') ?></h4>
                                <small class="text-muted"><?= TrdNC(11, 'Anticipo requerido para confirmar su reserva') ?></small>
                            </div>
                        </div>

                        <?php if (($paypal_account['Active'] ?? 0) == 1): ?>
                        <div class="mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="payment_method" id="method-card" checked autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm py-2" for="method-card">
                                    <i class="bi bi-credit-card me-1"></i> <?= TrdNC(12, 'Tarjeta') ?>
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="method-paypal" autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm py-2" for="method-paypal">
                                    <i class="bi bi-paypal me-1"></i> <?= TrdNC(13, 'PayPal') ?>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form id="payment-form" action="#" method="POST">
                            <input type="hidden" name="token_id" id="token_id">
                            <input type="hidden" name="token" id="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="amount" id="monto-final" value="<?php echo $lead['DepositAmount']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase"><?= TrdNC(14, 'Datos de Contacto') ?></label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm" name="name" id="client-name" placeholder="<?= TrdNC(15, 'Nombre') ?>" value="<?php echo htmlspecialchars($Nom, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm" name="last_name" id="client-lastname" placeholder="<?= TrdNC(16, 'Apellidos') ?>" value="<?php echo htmlspecialchars($Ape, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <input type="email" class="form-control form-control-sm" name="email" id="client-email" placeholder="<?= TrdNC(17, 'Correo Electrónico') ?>" value="<?php echo htmlspecialchars($Correo, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div id="traditional-gateway-section" class="mb-3">
                                <?php if ($PayPlatform == 'OPAY'): ?>
                                    <label class="form-label fw-bold small text-muted text-uppercase"><?= TrdNC(18, 'Datos de la Tarjeta') ?></label>
                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm" placeholder="<?= TrdNC(19, 'Nombre del titular') ?>" data-openpay-card="holder_name">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm only-numbers" placeholder="<?= TrdNC(20, 'Número de tarjeta') ?>" data-openpay-card="card_number" maxlength="16">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm only-numbers" placeholder="<?= TrdNC(21, 'MM') ?>" data-openpay-card="expiration_month" maxlength="2">
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm only-numbers" placeholder="<?= TrdNC(22, 'AA') ?>" data-openpay-card="expiration_year" maxlength="2">
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm only-numbers" placeholder="<?= TrdNC(23, 'CVV') ?>" data-openpay-card="cvv2" maxlength="4">
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <label class="form-label fw-bold small text-muted text-uppercase"><?= TrdNC(18, 'Datos de la Tarjeta') ?></label>
                                    <div id="card-container" class="mb-2 p-2 border rounded bg-light"></div>
                                <?php endif; ?>
                            </div>

                            <div id="paypal-gateway-section" class="mb-3 d-none">
                                <label class="form-label fw-bold small text-muted text-uppercase"><?= TrdNC(24, 'Pagar con PayPal') ?></label>
                                <div id="paypal-button-container"></div>
                            </div>

                            <!-- Resumen de Totales -->
                            <div class="bg-light p-3 rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                                    <span><?= TrdNC(25, 'Total cotización:') ?></span>
                                    <span class="fw-bold text-dark">$<?php echo number_format($lead['Total'], 2, '.', ','); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                                    <span><?= TrdNC(26, 'Balance pendiente:') ?></span>
                                    <span class="fw-bold text-dark">$<?php echo number_format($lead['Balance'], 2, '.', ','); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1 small text-muted d-none" id="div-pago-extra">
                                    <span><?= TrdNC(27, 'Recargo con tarjeta') ?> (<?= number_format((float)$extra_pctj * 100, 2) ?>%):</span>
                                    <span class="fw-bold" id="display-pago-extra">$0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold d-block text-primary"><?= TrdNC(29, 'Monto a pagar hoy') ?></span>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?= TrdNC(30, 'Depósito requerido') ?></small>
                                    </div>
                                    <h3 class="fw-bold mb-0 text-primary" id="display-pago-hoy">$<?php echo number_format($lead['DepositAmount'], 2, '.', ','); ?></h3>
                                </div>
                            </div>

                            <div id="payment-status-container" class="mb-3 text-center"></div>

                            <div id="traditional-buttons-section">
                                <?php if ($PayPlatform == 'OPAY'): ?>
                                    <button type="button" class="btn btn-pay w-100 py-3 shadow-sm" id="pay-button">
                                        <i class="bi bi-lock-fill me-2"></i><?= TrdNC(31, 'Pagar ahora') ?>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="card-button">
                                        <i class="bi bi-lock-fill me-2"></i><?= TrdNC(31, 'Pagar ahora') ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- PASO 2: SECCIÓN DE ÉXITO DE PAGO + FIRMA DIGITAL (SUCCESSPAYMENT + CONTRACT) -->
                    <div id="step-signature" style="<?= ($isAlreadyPaid && !$isAlreadySigned) ? '' : 'display: none;' ?>">
                        <!-- Resumen del Pago Exitoso -->
                        <div class="alert alert-success border-0 shadow-sm p-3 rounded-3 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-2 me-3"></i>
                                <div>
                                    <h5 class="fw-bold mb-0 text-success"><?= TrdNC(37, '¡Pago Realizado con Éxito!') ?></h5>
                                    <small class="text-muted"><?= TrdNC(38, 'Hemos recibido tu pago correctamente y tu reserva está garantizada.') ?></small>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row g-2 small pt-1">
                                <div class="col-6">
                                    <span class="text-muted"><?= TrdNC(39, 'Transacción') ?>:</span>
                                    <span class="fw-bold text-dark d-block text-truncate" id="success-tid-display"><?php echo $initialTId ?: 'OK-'.substr($token, 0, 8); ?></span>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted"><?= TrdNC(40, 'Monto pagado') ?>:</span>
                                    <span class="fw-bold text-success d-block" id="success-paid-display">+$<?php echo number_format($lead['DepositAmount'], 2, '.', ','); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Firma y Captura de Nombre -->
                        <div class="border rounded-3 p-3 bg-white">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-pen-fill text-primary fs-4 me-2"></i>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= TrdNC(41, 'Firma de Conformidad') ?></h5>
                                    <small class="text-muted"><?= TrdNC(42, 'Por favor dibuje su firma y escriba su nombre para generar el contrato.') ?></small>
                                </div>
                            </div>

                            <form id="signature-form">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase"><?= TrdNC(43, 'Nombre de quien firma') ?></label>
                                    <input type="text" id="signer-name" class="form-control" placeholder="<?= TrdNC(44, 'Escriba su nombre completo') ?>" value="<?php echo htmlspecialchars($Nom . ($Ape ? ' ' . $Ape : ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-0"><?= TrdNC(45, 'Firma digital') ?></label>
                                        <button type="button" id="clear-btn" class="btn btn-link btn-sm text-danger text-decoration-none p-0">
                                            <i class="bi bi-eraser-fill me-1"></i><?= TrdNC(46, 'Limpiar') ?>
                                        </button>
                                    </div>
                                    <div class="canvas-container">
                                        <canvas id="signature-pad"></canvas>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i><?= TrdNC(47, 'Dibuje su firma con el dedo o ratón en el cuadro superior.') ?>
                                    </small>
                                </div>

                                <div id="signature-status-container" class="mb-2"></div>

                                <button type="button" id="btn-sign-contract" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
                                    <i class="bi bi-file-earmark-check me-2"></i><?= TrdNC(48, 'Firmar y Generar Contrato') ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- PASO 3: SECCIÓN DE DESCARGA DE PDF (PDF GENERADO) -->
                    <div id="step-download" style="<?= $isAlreadySigned ? '' : 'display: none;' ?>">
                        <div class="text-center py-4">
                            <i class="bi bi-patch-check-fill text-success" style="font-size: 4rem;"></i>
                            <h3 class="fw-bold mt-3 mb-2 text-dark"><?= TrdNC(51, '¡Contrato Finalizado!') ?></h3>
                            <p class="text-muted mb-4 small">
                                <?= TrdNC(52, 'El contrato ha sido firmado digitalmente y se ha generado su comprobante oficial en formato PDF.') ?>
                            </p>

                            <div class="d-grid gap-2">
                                <a id="download-contract-btn" href="<?php echo $pdfDataUri; ?>" download="<?php echo $pdfNombre; ?>" class="btn btn-success btn-lg py-3 fw-bold shadow-sm">
                                    <i class="bi bi-download me-2"></i><?= TrdNC(53, 'Descargar Contrato PDF') ?>
                                </a>
                                <a href="<?php echo URL_BASE; ?>" class="btn btn-outline-secondary py-2 mt-2">
                                    <i class="bi bi-house me-2"></i><?= TrdNC(3, 'Volver al Inicio') ?>
                                </a>
                            </div>

                            <div class="mt-4 p-3 bg-light rounded-3 text-start border small text-muted">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?= TrdNC(54, 'Folio del evento:') ?></span>
                                    <strong class="text-dark">#<?= $lead['Folio'] ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?= TrdNC(55, 'Archivo:') ?></span>
                                    <strong class="text-dark"><?= $token ?>.pdf</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><?= TrdNC(56, 'Fecha de confirmación:') ?></span>
                                    <strong class="text-dark"><?= date('Y-m-d') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: CONTRATO (2/3 en pantalla grande) -->
        <div class="col-12 col-lg-8 col-xl-8">
            <div class="contract-paper">
                <div class="contract-header-bar">
                    <div>
                        <span class="text-uppercase text-muted fw-bold small d-block"><?= TrdNC(57, 'Documento Oficial') ?></span>
                        <h2 class="h4 fw-bold mb-0 text-dark"><?= TrdNC(58, 'Contrato de Servicio') ?></h2>
                        <small class="text-muted"><?= TrdNC(59, 'Folio de Cotización:') ?> #<?= $lead['Folio'] ?></small>
                    </div>
                    <div>
                        <span id="contract-badge-status" class="badge <?= $isAlreadySigned ? 'bg-success' : ($isAlreadyPaid ? 'bg-info' : 'bg-warning text-dark') ?> px-3 py-2 fs-6 rounded-pill">
                            <i class="bi <?= $isAlreadySigned ? 'bi-patch-check-fill' : ($isAlreadyPaid ? 'bi-credit-card' : 'bi-clock') ?> me-1"></i>
                            <?= $isAlreadySigned ? TrdNC(62, 'Firmado y Confirmado') : ($isAlreadyPaid ? TrdNC(61, 'Pago Acreditado') : TrdNC(60, 'Pendiente de Pago')) ?>
                        </span>
                    </div>
                </div>

                <div id="loader-container" class="loader-wrapper">
                    <div class="loader"></div>
                    <p class="text-muted small mt-2"><?= TrdNC(63, 'Cargando contenido del contrato...') ?></p>
                </div>

                <!-- Contenedor del Contrato Renderizado -->
                <div id="Contract" style="display: none;">
                    <?php
                        $api_url = URL_API."document_center";
                        $data = json_encode(['Tipo' => 'contract', 'IdTemplate' => 2, 'Idioma' => $lang]);
                        $Template = json_decode(API($jwt, $api_url, $data, 'GET'), true);
                        echo $Template['Template'] ?? '';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="scroll-indicator">
    <span>⬇ <?= TrdNC(64, 'Deslice para revisar contrato') ?> ⬇</span>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SCRIPTS DE PAGO Y PASARELAS -->
<?php if ($PayPlatform == 'OPAY'): ?>
<script>
    $(document).ready(function() {
        OpenPay.setId('<?php echo $opay_account['Id'] ?? ''; ?>');
        OpenPay.setApiKey('<?php echo $opay_account['PublicKey'] ?? ''; ?>');
        OpenPay.setSandboxMode('<?php echo $opay_account['sandbox'] ?? '1'; ?>');
        OpenPay.deviceData.setup("payment-form", "deviceIdHiddenFieldName");

        $('.only-numbers').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        $('#pay-button').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> <?= TrdNC(32, "Procesando pago...") ?>');

            let valid = true;
            $('#payment-form input[required]').each(function() {
                if ($(this).val().trim() === "") {
                    $(this).addClass('is-invalid');
                    valid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!valid) {
                alert("<?= TrdNC(33, 'Por favor complete todos los campos requeridos.') ?>");
                $btn.prop("disabled", false).html('<i class="bi bi-lock-fill me-2"></i><?= TrdNC(31, "Pagar ahora") ?>');
                return;
            }

            const statusDiv = document.getElementById('payment-status-container');
            statusDiv.innerHTML = "";

            OpenPay.token.extractFormAndCreate('payment-form', function(res) {
                var token_id = res.data.id;
                $('#token_id').val(token_id);

                var datosFormulario = $('#payment-form').serialize();

                $.ajax({
                    type: "POST",
                    url: url_api + 'processpayment',
                    data: JSON.stringify(Object.fromEntries(new URLSearchParams(datosFormulario))),
                    dataType: "json",
                    contentType: "application/json",
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'X-ID-CLIENT': '<?= ID_CLIENT ?>',
                        'LNG': '<?= $_SESSION['Idioma'] ?>'
                    },
                    success: function(respuestaBackend) {
                        if (respuestaBackend.status === 'success') {
                            const amounts = getCardPaymentAmounts();
                            handlePaymentSuccess({
                                transactionId: respuestaBackend.transaction_id || ('OPAY-' + Date.now()),
                                platform: 'OPAY',
                                amounts: amounts
                            });
                        } else if (respuestaBackend.status === 'pending') {
                            window.location.href = respuestaBackend.url;
                        } else {
                            statusDiv.innerHTML = `
                                <div class="alert alert-danger d-flex align-items-center mt-2 small">
                                    <span class="me-2">❌</span>
                                    <div>${respuestaBackend.message || "Error al procesar pago"}</div>
                                </div>`;
                            $btn.prop("disabled", false).html('<i class="bi bi-lock-fill me-2"></i><?= TrdNC(34, "Reintentar Pago") ?>');
                        }
                    },
                    error: function(err) {
                        var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno en el servidor.";
                        statusDiv.innerHTML = `
                            <div class="alert alert-danger d-flex align-items-center mt-2 small">
                                <span class="me-2">❌</span>
                                <div><?= TrdNC(35, 'Error en el cobro') ?>: ${errorMsg}</div>
                            </div>`;
                        $btn.prop("disabled", false).html('<i class="bi bi-lock-fill me-2"></i><?= TrdNC(34, "Reintentar Pago") ?>');
                    }
                });

            }, function(err) {
                var desc = err.data.description != undefined ? err.data.description : err.message;
                statusDiv.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center mt-2 small">
                        <span class="me-2">❌</span>
                        <div><?= TrdNC(36, 'Error en la tarjeta') ?>: ${desc}</div>
                    </div>`;
                $btn.prop("disabled", false).html('<i class="bi bi-lock-fill me-2"></i><?= TrdNC(34, "Reintentar Pago") ?>');
            });
        });
    });
</script>
<?php else: ?>
<script>
    const appId = '<?php echo $square_account['Id'] ?? ''; ?>';
    const locId = '<?php echo $square_account['LocalId'] ?? ''; ?>';

    async function initSquare() {
        if (!window.Square || !document.getElementById('card-container')) return;
        try {
            const payments = Square.payments(appId, locId);
            const card = await payments.card();
            await card.attach('#card-container');

            document.getElementById('card-button').addEventListener('click', async () => {
                try {
                    const result = await card.tokenize();
                    if (result.status === 'OK') {
                        await procesarPago(result.token);
                    }
                } catch (e) {
                    console.error('Error al tokenizar:', e);
                }
            });
        } catch(e) {
            console.error('Error al iniciar Square:', e);
        }
    }

    async function procesarPago(token_square) {
        const statusDiv = document.getElementById('payment-status-container');
        const payButton = document.getElementById('card-button');
        $('#token_id').val(token_square);

        payButton.disabled = true;
        payButton.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            <?= TrdNC(32, "Procesando pago...") ?>
        `;
        statusDiv.innerHTML = "";

        try {
            var datosFormulario = $('#payment-form').serialize();

            $.ajax({
                type: "POST",
                url: url_api + 'processpayment_square',
                data: JSON.stringify(Object.fromEntries(new URLSearchParams(datosFormulario))),
                dataType: "json",
                contentType: "application/json",
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'X-ID-CLIENT': '<?= ID_CLIENT ?>',
                    'LNG': '<?= $_SESSION['Idioma'] ?>'
                },                      
                success: function(respuestaBackend) {
                    if (respuestaBackend.status === 'success') {
                        const amounts = getCardPaymentAmounts();
                        handlePaymentSuccess({
                            transactionId: respuestaBackend.transaction_id || ('SQ-' + Date.now()),
                            platform: 'SQUARE',
                            amounts: amounts
                        });
                    } else {
                        statusDiv.innerHTML = `
                            <div class="alert alert-danger d-flex align-items-center mt-2 small">
                                <span class="me-2">❌</span>
                                <div>${respuestaBackend.message || "Error al procesar"}</div>
                            </div>`;
                        payButton.disabled = false;
                        payButton.innerHTML = "<?= TrdNC(34, 'Reintentar Pago') ?>";
                    }
                },
                error: function(err) {
                    var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno en el servidor.";
                    statusDiv.innerHTML = `
                        <div class="alert alert-danger d-flex align-items-center mt-2 small">
                            <span class="me-2">❌</span>
                            <div>${errorMsg}</div>
                        </div>`;
                    payButton.disabled = false;
                    payButton.innerHTML = "<?= TrdNC(34, 'Reintentar Pago') ?>";
                }
            });                
        } catch (error) {
            statusDiv.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center mt-2 small">
                    <span class="me-2">❌</span>
                    <div>${error.message}</div>
                </div>`;
            payButton.disabled = false;
            payButton.innerHTML = "<?= TrdNC(34, 'Reintentar Pago') ?>";
        }
    }

    $(document).ready(function() {
        initSquare();
    });
</script>
<?php endif; ?>

<!-- CÁLCULOS Y RESUMEN DE PAGO -->
<script>
    const montoBaseCents = <?= json_encode((int) round((float) $lead['DepositAmount'] * 100)) ?>;
    const extraPctj = <?= json_encode((float) $extra_pctj) ?>;
    const paypalExtraPctj = <?= json_encode((float) $paypal_extra_pctj) ?>;
    const paymentPlatform = <?= json_encode($PayPlatform === 'OPAY' ? 'OPAY' : 'SQUARE') ?>;
    const quoteUuid = '<?= $token ?>';

    function getCardPaymentAmounts() {
        const extraCents = extraPctj > 0
            ? Math.round(montoBaseCents * extraPctj + Number.EPSILON)
            : 0;

        return {
            baseAmount: (montoBaseCents / 100).toFixed(2),
            extraAmount: (extraCents / 100).toFixed(2),
            totalAmount: ((montoBaseCents + extraCents) / 100).toFixed(2)
        };
    }

    function getPayPalPaymentAmounts() {
        const extraCents = paypalExtraPctj > 0
            ? Math.round(montoBaseCents * paypalExtraPctj + Number.EPSILON)
            : 0;

        return {
            baseAmount: (montoBaseCents / 100).toFixed(2),
            extraAmount: (extraCents / 100).toFixed(2),
            totalAmount: ((montoBaseCents + extraCents) / 100).toFixed(2)
        };
    }

    function actualizarResumenPago() {
        const tipoPago = $('#method-paypal').length && $('#method-paypal').is(':checked')
            ? 'paypal'
            : 'tarjeta';
        const amounts = tipoPago === 'paypal'
            ? getPayPalPaymentAmounts()
            : getCardPaymentAmounts();
        const porcentaje = tipoPago === 'paypal' ? paypalExtraPctj : extraPctj;

        $('#monto-final').val(amounts.totalAmount);
        $('#div-pago-extra').toggleClass('d-none', parseFloat(amounts.extraAmount) <= 0);
        $('#div-pago-extra .text-muted').text(
            (tipoPago === 'paypal' ? '<?= TrdNC(28, "Cargo PayPal") ?> (' : '<?= TrdNC(27, "Recargo con tarjeta") ?> (') +
            (porcentaje * 100).toFixed(2) + '%):'
        );
        $('#display-pago-extra').text('$' + amounts.extraAmount);
        $('#display-pago-hoy').text('$' + amounts.totalAmount);
    }

    $(document).ready(function() {
        actualizarResumenPago();
        $('input[name="payment_method"]').on('change', actualizarResumenPago);
    });
</script>

<!-- SOPORTE PAYPAL -->
<?php if (($paypal_account['Active'] ?? 0) == 1): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= $paypal_account['Id'] ?>&currency=<?= $account['Currency'] ?>&enable-funding=venmo,paylater&buyer-country=<?= $account['Pais'] ?>"></script>
<script>
$(document).ready(function() {
    $('input[name="payment_method"]').on('change', function() {
        if ($('#method-paypal').is(':checked')) {
            $('#traditional-gateway-section, #traditional-buttons-section').addClass('d-none');
            $('#paypal-gateway-section').removeClass('d-none');
        } else {
            $('#paypal-gateway-section').addClass('d-none');
            $('#traditional-gateway-section, #traditional-buttons-section').removeClass('d-none');
        }
    });

    if (window.paypal) {
        paypal.Buttons({
            commit: true,
            style: {
                layout: 'vertical',
                shape: 'rect',
                label: 'paypal'
            },
            onInit: function(data, actions) {
                function checkForm() {
                    const name = $('#client-name').val();
                    const lastname = $('#client-lastname').val();
                    const email = $('#client-email').val();
                    if (name && lastname && email) {
                        actions.enable();
                    } else {
                        actions.disable();
                    }
                }
                checkForm();
                $('#client-name, #client-lastname, #client-email').on('keyup change', checkForm);
            },
            onClick: function() {
                if (!$('#client-name').val() || !$('#client-lastname').val() || !$('#client-email').val()) {
                    $('#payment-form')[0].reportValidity();
                }
            },
            createOrder: function(data, actions) {
                const amounts = getPayPalPaymentAmounts();
                const currency = '<?= $account['Currency'] ?>';
                const items = [{
                    name: 'Pago de cotización #<?= $lead['Folio'] ?>',
                    sku: 'Payment',
                    unit_amount: {
                        currency_code: currency,
                        value: amounts.baseAmount
                    },
                    quantity: '1'
                }];

                if (parseFloat(amounts.extraAmount) > 0) {
                    items.push({
                        name: 'Cargo PayPal (' + (paypalExtraPctj * 100).toFixed(2) + '%)',
                        sku: 'PayPalFee',
                        unit_amount: {
                            currency_code: currency,
                            value: amounts.extraAmount
                        },
                        quantity: '1'
                    });
                }
                
                return actions.order.create({
                    application_context: {
                        shipping_preference: 'NO_SHIPPING'
                    },
                    purchase_units: [{
                        amount: {
                            currency_code: currency,
                            value: amounts.totalAmount,
                            breakdown: {
                                item_total: {
                                    currency_code: currency,
                                    value: amounts.totalAmount
                                }
                            }
                        },
                        items: items
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(orderData) {
                    const amounts = getPayPalPaymentAmounts();
                    $('#monto-final').val(amounts.totalAmount);
                    var formData = $('#payment-form').serialize();
                    formData += '&orderID=' + encodeURIComponent(data.orderID);

                    $.ajax({
                        type: "POST",
                        url: url_api + 'processpayment_paypal',
                        data: JSON.stringify(Object.fromEntries(new URLSearchParams(formData))),                    
                        dataType: 'json',
                        contentType: "application/json",
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'X-ID-CLIENT': '<?= ID_CLIENT ?>',
                            'LNG': '<?= $_SESSION['Idioma'] ?>'
                        },                        
                        beforeSend: function() {
                            $('#payment-status-container').html('<div class="spinner-border spinner-border-sm text-primary"></div> <?= TrdNC(68, "Procesando orden PayPal...") ?>');
                        },
                        success: function(respuestaBackend) {
                            if (respuestaBackend.status === 'success') {
                                handlePaymentSuccess({
                                    transactionId: data.orderID || ('PAYPAL-' + Date.now()),
                                    platform: 'PAYPAL',
                                    amounts: amounts
                                });
                            } else {
                                $('#payment-status-container').html('<div class="alert alert-danger small">' + (respuestaBackend.message || "<?= TrdNC(69, 'Error al procesar PayPal') ?>") + '</div>');
                            }
                        }
                    });
                });
            }
        }).render('#paypal-button-container');
    }
});
</script>
<?php endif; ?>

<!-- MANEJO DE PASOS (STEPPER), FIRMA Y PDF -->
<script>
    let signaturePad = null;

    function setStepperStep(step) {
        if (step >= 1) {
            if (step > 1) {
                $('#step-item-1').removeClass('active').addClass('completed');
                $('#step-num-1').html('<i class="bi bi-check-lg"></i>');
                $('#step-divider-1').addClass('completed');
            } else {
                $('#step-item-1').addClass('active').removeClass('completed');
                $('#step-num-1').text('1');
                $('#step-divider-1').removeClass('completed');
            }
        }
        if (step >= 2) {
            if (step > 2) {
                $('#step-item-2').removeClass('active').addClass('completed');
                $('#step-num-2').html('<i class="bi bi-check-lg"></i>');
                $('#step-divider-2').addClass('completed');
            } else {
                $('#step-item-2').addClass('active').removeClass('completed');
                $('#step-num-2').text('2');
                $('#step-divider-2').removeClass('completed');
            }
        } else {
            $('#step-item-2').removeClass('active completed');
            $('#step-num-2').text('2');
            $('#step-divider-2').removeClass('completed');
        }
        if (step >= 3) {
            $('#step-item-3').addClass('active completed');
            $('#step-num-3').html('<i class="bi bi-check-lg"></i>');
        } else {
            $('#step-item-3').removeClass('active completed');
            $('#step-num-3').text('3');
        }
    }

    function initSignaturePad() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) return;
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 80)'
        });
        resizeCanvas();
    }

    function resizeCanvas() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas || !canvas.offsetWidth) return;
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        let data = (signaturePad && !signaturePad.isEmpty()) ? signaturePad.toDataURL() : null;

        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);

        if (signaturePad) {
            if (data) {
                signaturePad.fromDataURL(data);
            } else {
                signaturePad.clear();
            }
        }
    }

    window.addEventListener("resize", resizeCanvas);

    // Cuando el pago resulta exitoso
    function handlePaymentSuccess(info) {
        try {
            localStorage.removeItem('ds_jumper_cart');
            if (typeof renderizarCarrito === 'function') {
                renderizarCarrito();
            }
        } catch(e) {}

        const tid = info.transactionId || ('TR-' + Date.now());
        $('#success-tid-display').text(tid);

        if (info.amounts && info.amounts.totalAmount) {
            $('#success-paid-display').text('+$' + info.amounts.totalAmount);
        }

        // Si el cliente capturó nombre en el pago, sugerirlo para la firma
        const fullName = ($('#client-name').val() + ' ' + $('#client-lastname').val()).trim();
        if (fullName && !$('#signer-name').val()) {
            $('#signer-name').val(fullName);
        }

        setStepperStep(2);

        $('#step-payment').slideUp(300, function() {
            $('#step-signature').slideDown(400, function() {
                initSignaturePad();
                resizeCanvas();
            });
        });

        $('#contract-badge-status')
            .removeClass('bg-warning text-dark')
            .addClass('bg-info text-white')
            .html('<i class="bi bi-credit-card me-1"></i> <?= TrdNC(61, "Pago Acreditado") ?>');

        $('.sticky-workflow-card').scrollTop(0);

        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
            alert("<?= TrdNC(66, 'La transacción ya fue procesada.') ?>");
        };
    }

    // Cuando el PDF es generado
    function handlePdfGenerated(data) {
        setStepperStep(3);

        $('#step-signature').slideUp(300, function() {
            $('#step-download').slideDown(400);
        });

        const docPath = (data && data.document) ? data.document : (quoteUuid + '.pdf');
        $('#download-contract-btn').attr('href', docPath + '?v=' + Date.now());
        $('#download-contract-btn').attr('download', docPath);

        $('#contract-badge-status')
            .removeClass('bg-warning text-dark bg-info')
            .addClass('bg-success text-white')
            .html('<i class="bi bi-patch-check-fill me-1"></i> <?= TrdNC(62, "Firmado y Confirmado") ?>');

        $('.sticky-workflow-card').scrollTop(0);
    }

    $(document).ready(function() {
        // Inicializar firma si ya está en paso 2
        if ($('#step-signature').is(':visible')) {
            initSignaturePad();
            setTimeout(resizeCanvas, 200);
        }

        $('#clear-btn').click(function() {
            if (signaturePad) {
                signaturePad.clear();
            }
        });

        // Botón Firmar y Generar Contrato
        $('#btn-sign-contract').click(function(e) {
            e.preventDefault();

            if (!signaturePad || signaturePad.isEmpty()) {
                alert("<?= TrdNC(49, 'Por favor dibuje su firma antes de continuar') ?>");
                return;
            }

            const signerName = $('#signer-name').val().trim();
            if (!signerName) {
                alert("<?= TrdNC(44, 'Escriba su nombre completo') ?>");
                $('#signer-name').focus();
                return;
            }

            const dataURL = signaturePad.toDataURL('image/png');

            // Reflejar visualmente la firma en el contrato del lado derecho
            $('#img-firma-tabla').attr('src', dataURL).show();

            let contenido = $('#Contract').html();
            contenido = contenido.replace(/\*signeddate\*/g, "<?= date('Y-m-d') ?>")
                                 .replace(/\*customerdsname\*/g, signerName);
            $('#Contract').html(contenido);

            const contenidoDiv = document.getElementById('Contract').innerHTML;
            const datos = {
                token: quoteUuid,
                contrato: contenidoDiv
            };

            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> <?= TrdNC(50, 'Generando documento...') ?>');

            fetch('pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en el servidor al generar PDF');
                }
                return response.json();
            })
            .then(data => {
                handlePdfGenerated(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert("<?= TrdNC(67, 'Ocurrió un error al generar el PDF. Por favor intente nuevamente.') ?>");
                $btn.prop('disabled', false).html('<i class="bi bi-file-earmark-check me-2"></i><?= TrdNC(48, 'Firmar y Generar Contrato') ?>');
            });
        });

        // Control indicador de scroll
        $(window).scroll(function() {
            if ($(window).scrollTop() > 300) {
                $('#scroll-indicator').css('opacity', '0');
            } else {
                $('#scroll-indicator').css('opacity', '1');
            }
        });

        LoadDocument();

        $("#loader-container").fadeOut(400, function() {
            $("#Contract").fadeIn(500); 
        });
    });

    // RENDERIZADO DEL CONTRATO EN EL LADO DERECHO
    function LoadDocument() {
        <?php
            $FHI = $lead['StartDateTime'] ?? '';
            $FHF = $lead['EndDateTime'] ?? '';
        ?>

        const FHI = '<?= $FHI ?>';
        const FHF = '<?= $FHF ?>';
        const FHIp = FHI.split(' ');
        const FHFp = FHF.split(' ');

        function formatTime12Hour(time) {
            if (!time) return '';
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours, 10);
            const period = hour >= 12 ? 'PM' : 'AM';
            return `${hour % 12 || 12}:${minutes} ${period}`;
        }

        const datosGenerales = {
            leadid: "<?= $lead['Folio'] ?>",
            contractsentdate: "<?= date('Y-m-d') ?>",
            company_logo: "https://pub-546a2d9d368f4b87a457962de1d77955.r2.dev/<?= $account['Id'] ?>/account/originals/<?= $account['Logo'] ?>",
            company_name: <?= json_encode($account['NombreCompania'] ?? '') ?>,
            company_address: <?= json_encode(($account['Direccion'] ?? '').' '.($account['Direccion2'] ?? '')) ?>,
            company_city: <?= json_encode(($account['Ciudad'] ?? '').' '.($account['CP'] ?? '')) ?>,
            company_state: <?= json_encode($account['Estado'] ?? '') ?>,
            company_phone: <?= json_encode($account['TelefonoCelular'] ?? '') ?>,

            organization: <?= json_encode($organization['Nombre'] ?? '') ?>,
            ctfirstname: <?= json_encode($customer['Nombres'] ?? '') ?>,
            ctlastname: <?= json_encode($customer['Apellidos'] ?? '') ?>,
            eventstreet: <?= json_encode($venue['Direccion'] ?? '') ?>,
            eventcity: <?= json_encode($venue['Ciudad'] ?? '') ?>,
            eventstate: <?= json_encode($venue['Estado'] ?? '') ?>,
            eventzip: <?= json_encode($venue['CP'] ?? '') ?>,
            phones: <?= json_encode($venue['Telefono'] ?? '') ?>,
            startdate: FHIp[0] || '',
            starttime: formatTime12Hour(FHIp[1] || ''),
            enddate: FHFp[0] || '',
            endtime: formatTime12Hour(FHFp[1] || ''),
            deliverytype: "ON SITE",

            itemtotals: "<?= $lead['ItemTotals'] ?? '' ?>",
            distanecharges: "<?= $lead['DistanceCharges'] ?? '' ?>",
            staffcosts: "<?= $lead['StafCost'] ?? '' ?>",
            discount: "<?= $lead['Discount'] ?? '' ?>",

            subtotal: "<?= $lead['SubTotal'] ?? '' ?>",
            taxexcempt: "<?= $lead['TaxId'] ?? '' ?>",
            taxrate: "<?= $lead['TaxPc'] ?? '' ?>",
            salestax: "<?= $lead['TaxAmount'] ?? '' ?>",
            tip: "<?= $lead['Tip'] ?? '' ?>",
            total: "<?= $lead['Total'] ?? '' ?>",
            apayment: "<?= $lead['DepositAmount'] ?? '' ?>",
            cardcharge: <?= json_encode($card_charge_percentage > 0 ? (float) $lead['DepositAmount'] * ($card_charge_percentage / 100) : 0) ?>,
            atopay: <?= json_encode($card_charge_percentage > 0 ? (float) $lead['DepositAmount'] * (1 + ($card_charge_percentage / 100)) : 0) ?>,
            ctr_balance_due: "<?= $lead['Total'] ?? '' ?>",
            electric: ""
        };

        const productos = [];
        const descuentos = [];

        <?php
            echo $respuesta['script_push'] ?? '';
            if ($discounts) {
                foreach ($discounts as $discount) {
                    echo "
                    let descuento".$discount['Id']." = {
                        concepto: ".json_encode($discount['Name'] ?? '').",
                        monto: ".json_encode($discount['AmountVal'] ?? '')."
                    };
                    descuentos.push(descuento".$discount['Id'].");";
                }
            }
            if ($gifcrds) {
                foreach ($gifcrds as $discount) {
                    echo "
                    let descuento".$discount['Id']." = {
                        concepto: ".json_encode($discount['Descript'] ?? '').",
                        monto: ".json_encode($discount['AmountVal'] ?? '')."
                    };
                    descuentos.push(descuento".$discount['Id'].");";
                }
            }
        ?>

        const $contenedor = $('#contrato-dsj');
        const $cuerpoTabla = $('#lista-productos');
        const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
        ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos, descuentos);

        <?php if (($lead['Tip'] ?? 0) > 0): ?>
            $('#tips').show();
        <?php else: ?>
            $('#tips').hide();
        <?php endif; ?>
    }

    function ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos, descuentos) {
        if (!$cuerpoTabla.length || !$filaPlantilla.length) return;

        $cuerpoTabla.find('tr:not(.item-fila)').remove();

        productos.forEach(producto => {
            let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
            $.each(producto, function(key, val) {
                let regex = new RegExp('\\*' + key + '\\*', 'g');
                nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
            });
            let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', '');
            $cuerpoTabla.append($nuevaFila);
        });

        const $contenedorDescuentos = $contenedor.find('#extra_discounts');
        const $filaOriginal = $contenedorDescuentos.find('tr').first();

        if ($filaOriginal.length > 0) {
            const htmlPlantillaDesc = $filaOriginal[0].outerHTML;
            $contenedorDescuentos.empty();

            descuentos.forEach(desc => {
                let gfcrd = '';
                const codigo = desc.concepto;
                const regex = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
                if (regex.test(codigo)) {
                    gfcrd = 'Tarjeta de regalo<br>';
                }     

                let filaDescHtml = htmlPlantillaDesc
                    .replace('*conceptdiscount*', gfcrd + desc.concepto)
                    .replace('*discountconcept*', desc.monto);
                $contenedorDescuentos.append(filaDescHtml);
            });
        }

        $.each(datosGenerales, function(key, val) {
            let $elemento = $contenedor.find('#' + key);
            if (val === 0 || val === "0") {
                $elemento.hide();
            } else {
                $elemento.show();
            }

            let regex = new RegExp('\\*' + key + '\\*', 'g');
            let contenidoActual = $contenedor.html();
            $contenedor.html(contenidoActual.replace(regex, val ?? ''));
        });

        <?php if ($card_charge_percentage > 0): ?>
            $contenedor.find('#tr-cardcharge, #tr-atopay').show();
        <?php else: ?>
            $contenedor.find('#tr-cardcharge, #tr-atopay').hide();
        <?php endif; ?>
    }

    <?php if (($account['Deposit'] ?? 0) == 0): ?>
        $('#tr-apay').hide();
    <?php endif; ?>
</script>

<?php require_once TEMPLATE.'social.php'; ?>
<?php require_once TEMPLATE.'cart.php'; ?>
<?php require_once TEMPLATE.'scripts.php'; ?>
<?php require_once 'scripts.php'; ?>
<script src="<?php echo TEMPLATE;?>js/idx-template.js"></script>
<script src="js/general.php"></script>
</body>
</html>

