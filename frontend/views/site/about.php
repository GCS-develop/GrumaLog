<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Acerca de — Términos (resumen)';
$this->params['breadcrumbs'][] = ['label' => 'Inicio', 'url' => ['site/index']];
$this->params['breadcrumbs'][] = 'Acerca de';

// Opcional: estilos ligeros para tipografía si no usas utilidades tipo "prose"
$this->registerCss(<<<CSS
.prose h2, .prose h3 { margin-top: 1.25rem; }
.prose p, .prose li { line-height: 1.6; }
.prose ul { padding-left: 1.25rem; }
.anchor { scroll-margin-top: 80px; }
CSS);

// Última actualización visible para usuarios
$lastUpdated = 'Octubre 3, 2025';
?>

<div class="site-about container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 prose">

                    <header class="mb-3">
                        <h1 class="h4 mb-1"><?= Html::encode($this->title) ?></h1>
                        <small class="text-muted">Última actualización: <?= Html::encode($lastUpdated) ?></small>
                    </header>

                    <p class="mb-4">
                        Este es un resumen práctico de nuestros Términos. Para la versión vinculante y completa,
                        <?= Html::a('consúltala aquí', 'https://herpo.org/policies/terms-of-service', [
                            'target' => '_blank',
                            'rel' => 'noopener',
                        ]) ?>.
                    </p>

                    <!-- Índice corto -->
                    <nav aria-label="Índice de secciones" class="mb-3">
                        <ul class="list-inline small">
                            <li class="list-inline-item"><a href="#uso-permitido">Uso permitido</a></li>
                            <li class="list-inline-item"><a href="#cuentas-seguridad">Cuentas y seguridad</a></li>
                            <li class="list-inline-item"><a href="#compras-precios">Compras y precios</a></li>
                            <li class="list-inline-item"><a href="#envios-cambios">Envíos y devoluciones</a></li>
                            <li class="list-inline-item"><a href="#propiedad-intelectual">Propiedad intelectual</a></li>
                            <li class="list-inline-item"><a href="#contenido-usuario">Contenido del usuario</a></li>
                            <li class="list-inline-item"><a href="#conductas-prohibidas">Conductas prohibidas</a></li>
                            <li class="list-inline-item"><a href="#privacidad-datos">Privacidad y datos</a></li>
                            <li class="list-inline-item"><a href="#enlaces-terceros">Enlaces a terceros</a></li>
                            <li class="list-inline-item"><a href="#garantias-resp">Garantías y responsabilidad</a></li>
                            <li class="list-inline-item"><a href="#modificaciones">Modificaciones</a></li>
                            <li class="list-inline-item"><a href="#ley-aplicable">Ley aplicable</a></li>
                        </ul>
                    </nav>

                    <section id="intro" class="anchor">
                        <h2>Términos y condiciones (resumen)</h2>
                        <p>
                            Al usar nuestros sitios y servicios, aceptas estos términos y la normatividad aplicable en Colombia.
                            Si no estás de acuerdo, por favor no uses la plataforma.
                            <?= Html::a('Lee los Términos completos', 'https://herpo.org/policies/terms-of-service', [
                                'target' => '_blank',
                                'rel' => 'noopener',
                            ]) ?>.
                        </p>
                    </section>

                    <section id="uso-permitido" class="anchor">
                        <h3>Uso permitido</h3>
                        <ul>
                            <li>Prohibido el acoso, lenguaje abusivo u ofensivo, o afectar el funcionamiento del sitio.</li>
                            <li>No intentes acceder sin autorización a cuentas, sistemas o información.</li>
                        </ul>
                    </section>

                    <section id="cuentas-seguridad" class="anchor">
                        <h3>Cuentas y seguridad</h3>
                        <ul>
                            <li>Eres responsable de custodiar tus credenciales y las actividades realizadas con tu cuenta.</li>
                            <li>Reporta de inmediato accesos no autorizados o anomalías.</li>
                        </ul>
                    </section>

                    <section id="compras-precios" class="anchor">
                        <h3>Compras, precios y disponibilidad</h3>
                        <ul>
                            <li>Precios, promociones y disponibilidad pueden cambiar sin previo aviso.</li>
                            <li>Podemos limitar o rechazar pedidos ante errores, sospecha de fraude o restricciones logísticas.</li>
                        </ul>
                    </section>

                    <section id="envios-cambios" class="anchor">
                        <h3>Envíos, cambios y devoluciones</h3>
                        <ul>
                            <li>Aplican las políticas publicadas de tiempos de entrega, cambios y devoluciones.</li>
                            <li>Costos y condiciones de envío se informan durante la compra.</li>
                        </ul>
                    </section>

                    <section id="propiedad-intelectual" class="anchor">
                        <h3>Propiedad intelectual</h3>
                        <ul>
                            <li>Marcas, textos, imágenes y demás contenidos pertenecen a sus titulares y están protegidos por la ley.</li>
                            <li>No se permite reproducir, distribuir o usar contenido sin autorización.</li>
                        </ul>
                    </section>

                    <section id="contenido-usuario" class="anchor">
                        <h3>Contenido del usuario</h3>
                        <ul>
                            <li>Si envías reseñas o comentarios, declaras tener derecho a publicarlos y autorizas su uso según los términos.</li>
                        </ul>
                    </section>

                    <section id="conductas-prohibidas" class="anchor">
                        <h3>Conductas prohibidas</h3>
                        <ul>
                            <li>Publicar contenido ilegal, difamatorio, discriminatorio o que infrinja derechos de terceros.</li>
                            <li>Usar el sitio con fines fraudulentos, distribuir malware o interferir con su operación.</li>
                        </ul>
                    </section>

                    <section id="privacidad-datos" class="anchor">
                        <h3>Privacidad y datos personales</h3>
                        <ul>
                            <li>Tratamiento de datos conforme a la legislación colombiana y a nuestras políticas de datos personales.</li>
                            <li>
                                Revisa también la política de privacidad para finalidades, canales de recolección y derechos de titulares:
                                <?= Html::a('Política de privacidad', Url::to(['site/privacy'])) // ajusta la ruta si tu acción/página difiere 
                                ?>
                            </li>
                        </ul>
                    </section>

                    <section id="enlaces-terceros" class="anchor">
                        <h3>Enlaces a terceros</h3>
                        <ul>
                            <li>Los enlaces externos son de conveniencia; no somos responsables de su contenido o prácticas.</li>
                        </ul>
                    </section>

                    <section id="garantias-resp" class="anchor">
                        <h3>Garantías y limitación de responsabilidad</h3>
                        <ul>
                            <li>El sitio y sus contenidos se ofrecen “tal cual”, sin garantías implícitas en la medida permitida por la ley.</li>
                            <li>No seremos responsables por daños indirectos o consecuenciales derivados del uso del sitio.</li>
                        </ul>
                    </section>

                    <section id="modificaciones" class="anchor">
                        <h3>Modificaciones</h3>
                        <ul>
                            <li>Podemos actualizar estos términos en cualquier momento. El uso continuado implica aceptación de los cambios.</li>
                        </ul>
                    </section>

                    <section id="ley-aplicable" class="anchor">
                        <h3>Ley aplicable</h3>
                        <ul>
                            <li>Estos términos se rigen por las leyes de la República de Colombia.</li>
                        </ul>
                    </section>

                    <footer class="mt-3">
                        <strong>Texto completo de los Términos:</strong>
                        <?= Html::a('https://herpo.org/policies/terms-of-service', 'https://herpo.org/policies/terms-of-service', [
                            'target' => '_blank',
                            'rel' => 'noopener',
                        ]) ?>
                    </footer>

                </div>
            </div>
        </div>
    </div>
</div>