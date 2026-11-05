<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Bangers&family=Permanent+Marker&family=Comic+Neue:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    :root {
        --ink: #0a0a0a;
        --paper: #fdf6e3;
        --red: #e8001c;
        --blue: #0033cc;
        --yellow: #ffe600;
        --white: #ffffff;
        --border: 4px solid var(--ink);
        --shadow: 5px 5px 0 var(--ink);
    }

    body {
        background: var(--paper);
        font-family: 'Comic Neue', cursive;
        color: var(--ink);
        background-image: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(180,150,80,.03) 3px, rgba(180,150,80,.03) 4px);
    }

    /* Estilo para Títulos tipo Comic */
    h4, h5 {
        font-family: 'Bangers', cursive;
        letter-spacing: 2px;
        -webkit-text-stroke: 1px var(--ink);
        paint-order: stroke fill;
        text-shadow: 2px 2px 0 var(--ink);
    }

    /* Estilo de Tarjetas como Paneles de Comic */
    .card.comic-panel {
        background: var(--white);
        border: var(--border) !important;
        box-shadow: var(--shadow) !important;
        border-radius: 0 !important;
        background-image: radial-gradient(circle, rgba(0,0,0,.04) 1px, transparent 1px);
        background-size: 8px 8px;
    }

    .card-header.comic-header {
        background: var(--ink) !important;
        border-bottom: var(--border);
        color: var(--yellow) !important;
    }

    /* Inputs y Selects */
    .form-control, .form-select {
        border: 3px solid var(--ink);
        border-radius: 0;
        font-family: 'Comic Neue', cursive;
        font-weight: 700;
        box-shadow: 3px 3px 0 var(--ink);
    }

    .form-control:focus, .form-select:focus {
        background: #fffbe0;
        border-color: var(--ink);
        box-shadow: 4px 4px 0 var(--ink);
        outline: none;
    }

    .form-label {
        font-family: 'Bangers', cursive;
        letter-spacing: 1px;
        color: var(--ink);
        text-transform: uppercase;
    }

    /* Botones de Acción (Hero Buttons) */
    .btn-comic {
        font-family: 'Bangers', cursive;
        font-size: 1.2rem;
        letter-spacing: 2px;
        border: 3px solid var(--ink);
        border-radius: 0;
        box-shadow: var(--shadow);
        transition: .12s;
        text-transform: uppercase;
        clip-path: polygon(10px 0%, calc(100% - 10px) 0%, 100% 10px, 100% calc(100% - 10px), calc(100% - 10px) 100%, 10px 100%, 0% calc(100% - 10px), 0% 10px);
    }

    .btn-comic:hover {
        transform: translateY(-3px) rotate(-1deg);
        box-shadow: 7px 7px 0 var(--ink);
    }

    .btn-primary.btn-comic { background: var(--blue); color: var(--white); }
    .btn-success.btn-comic { background: var(--red); color: var(--white); } /* Ajustado al estilo red del comic */

    /* Resumen de totales */
    .text-primary { color: var(--blue) !important; }
    #checkout_total { font-family: 'Bangers', cursive; font-size: 2.5rem !important; }
</style>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-7">
            <h4 class="mb-4 fw-bold text-uppercase">💥 <?= Trd(21) ?></h4>
            
            <div class="card comic-panel mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning p-2 border border-dark me-3">
                            <i class="fa-solid fa-user text-dark"></i>
                        </div>
                        <h5 class="mb-0 fw-bold"><?= Trd(22) ?></h5>
                    </div>
                    <input type="hidden" id="Id">
                    <input type="hidden" id="Type">
                    <input type="hidden" id="Idgfc">
                    <input type="hidden" id="Agfc">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(23) ?></label>
                            <input type="text" id="nombre_cliente" class="form-control" placeholder="<?= Trd(23) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(24) ?></label>
                            <input type="text" id="apellidos" class="form-control">
                        </div>                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(25) ?></label>
                            <input type="text" id="organizacion" class="form-control">
                        </div>                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(26) ?></label>
                            <input type="tel" id="tel_cliente" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold"><?= Trd(27) ?></label>
                            <input type="email" id="email_cliente" class="form-control">
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <label class="form-label small fw-bold"><?= Trd(28) ?></label>
                            <input type="text" id="dir_cliente" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(29) ?></label>
                            <input type="text" id="ciudad_cliente" class="form-control">
                        </div>                        
                        <?php if ($account['Pais'] == 'MX'){?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(30) ?></label>
                            <input type="text" id="colonia_cliente" class="form-control">
                        </div>
                        <?php }?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(31) ?></label>
                            <input type="text" id="cp_cliente" class="form-control">
                        </div>
                    </div>                    
                </div>
            </div>

            <div class="card comic-panel">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info p-2 border border-dark me-3">
                                    <i class="fa-solid fa-location-dot text-white"></i>
                                </div>
                                <h5 class="mb-0 fw-bold"><?= Trd(32) ?></h5>
                            </div>
                        </div>
                        <div class="col-sm-6 text-end">
                            <button type="button" id="btn_copiar_direccion" class="btn btn-primary btn-comic w-100">
                                <?= Trd(33) ?> <i class="fa-regular fa-copy"></i>
                            </button>                        
                        </div>
                    </div>
                    <div class="alert alert-warning comic-panel p-3 mb-3" role="alert">
                        <strong><?= Trd(55) ?></strong> <?= Trd(56) ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="acepta_restricciones_ubicacion">
                            <label class="form-check-label" for="acepta_restricciones_ubicacion">
                                <?= Trd(57) ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold"><?= Trd(28) ?></label>
                            <input type="text" id="dir_evento" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(29) ?></label>
                            <input type="text" id="ciudad_evento" class="form-control">
                        </div>                           
                        <?php if ($account['Pais'] == 'MX'){?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(30) ?></label>
                            <input type="text" id="colonia_evento" class="form-control">
                        </div>
                        <?php }?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(31) ?></label>
                            <input type="text" id="cp_evento" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold"><?= Trd(34) ?></label>
                            <textarea id="ref_evento" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(35) ?></label>
                            <select class="form-select" id="superficie">
                                <option value="" selected><?= Trd(36) ?></option>
                                <?php
                                    $api_url = URL_API."surfaces";
                                    $data = json_decode(API($jwt,$api_url,'','POST'), true);
                                    if (isset($data['status']) && $data['status'] === 'success') {
                                        foreach ($data['data'] as $surface) {
                                            if (strtoupper($_SESSION['Idioma']) == 'ES') {
                                                echo "<option value='".$surface['Id']."'>".$surface['Nombre']."</option>";
                                            } else {
                                                echo "<option value='".$surface['Id']."'>".$surface['Name']."</option>";
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(37) ?></label>
                            <select class="form-select" id="tipo_entrega">
                                <option value="" selected><?= Trd(36) ?></option>
                                <option value='1'><?= Trd(38) ?></option>
                                <option value='2'><?= Trd(54) ?></option>
                            </select>                                
                        </div>
                        <div class="col-md-6">
                            <!--
                            <label class="form-label small fw-bold">Excluir Impuestos</label>
                            <input type="text" id="tax" class="form-control">
                            -->
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><?= Trd(39) ?></label>
                            <div class="input-group">
                                <input type="text" id="cupon" class="form-control" placeholder="ZAP! POW! CÓDIGO...">
                                <button class="btn btn-action" type="button" id="btn-validar-cupon" onclick="validar_cupon()">
                                    <?= Trd(40) ?>
                                </button>
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="sticky-top" style="top: 2rem;z-index: 900;">
                <div class="card comic-panel overflow-hidden">
                    <div class="card-header comic-header py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-receipt me-2"></i><?= Trd(41) ?></h5>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="p-4 bg-light border-bottom border-dark">
                            <div class="row text-center">
                                <div class="col-12 mb-3">
                                    <span class="d-block small text-muted text-uppercase fw-bold"><?= Trd(42) ?></span>
                                    <span id="resumen_fecha" class="fs-4 fw-bold text-primary"><?= Trd(43) ?></span>
                                </div>
                                <div class="col-6 border-end border-dark">
                                    <span class="d-block small text-muted text-uppercase fw-bold"><?= Trd(44) ?></span>
                                    <span id="resumen_hInicio" class="fw-bold fs-5">--:--</span>
                                </div>
                                <div class="col-6">
                                    <span class="d-block small text-muted text-uppercase fw-bold"><?= Trd(45) ?></span>
                                    <span id="resumen_hFin" class="fw-bold fs-5">--:--</span>
                                </div>
                            </div>
                        </div>

                        <div id="checkout_items" class="p-4" style="max-height: 400px; overflow-y: auto;">
                            </div>

                        <div class="p-4 bg-light border-top border-dark">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-bold"><?= Trd(46) ?></span>
                                <span id="checkout_subtotal" class="fw-bold">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span id="chEtiquetaCupon" class="text-muted fw-bold"><?= Trd(47) ?></span>
                                <span id="chDescuento" class="fw-bold text-danger">-$0.00</span>
                            </div>                             
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text comic-label-small"><?= Trd(48) ?></span>
                                    <input type="text" id="gifcardcode" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="20">
                                    <button class="btn btn-action" type="button" id="btn-validar"><?= Trd(49) ?></button>
                                </div>
                                <span id="chTarjetaRegalo" class="fw-bold ms-2">$0.00</span>
                            </div>
                            <hr class="border-dark opacity-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 fw-bold"><?= Trd(50) ?></span>
                                <div class="text-end">
                                    <span id="checkout_total" class="h2 mb-0 fw-bold text-primary">$0.00</span>
                                    <small class="d-block text-muted fw-bold" style="font-size: 0.7rem;"><?= Trd(51) ?></small>
                                </div>
                            </div>
                            
                            <button type="button" id="btn_enviar_cotizacion" class="btn btn-success btn-comic btn-lg w-100 mt-4 shadow">
                                <?= Trd(52) ?> <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <p class="text-center mt-3 small text-muted fw-bold">
                    <i class="fa-solid fa-lock me-1"></i> <?= Trd(53) ?>
                </p>
            </div>
        </div>
    </div>
</div>