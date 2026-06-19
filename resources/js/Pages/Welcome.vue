    <script setup>
    import { ref, onMounted, nextTick } from 'vue';
    import { Head, Link } from '@inertiajs/vue3';

    defineProps({
        canLogin: Boolean,
        canRegister: Boolean,
        laravelVersion: String,
        phpVersion: String,
    });

    const technicalSections = [
    {
        title: 'Arquitectura del SaaS',
        text: 'Laravel, Inertia.js y Vue 3 trabajan juntos para crear una experiencia SPA sin separar el backend del frontend en repositorios distintos.'
    },
    {
        title: 'Multi-Tenant',
        text: 'Cada empresa trabaja con datos aislados para evitar cruces de información entre clientes.'
    },
    {
        title: 'Modelo EAV',
        text: 'Permite agregar campos dinámicos sin modificar constantemente el esquema principal de la base de datos.'
    }
]

    // --- ESTADOS REACTIVOS ---
    const currentView = ref('inicio'); 
    const isTransitioning = ref(false); 

    // Tema Oscuro / Claro
    const isDarkMode = ref(false);

    // Estado del Modal y Formulario
    const isModalOpen = ref(false);
    const modalMessage = ref('');
    const leadForm = ref(null);

    // --- ANIMACIÓN SCROLL REVEAL ---
    let observer = null;

    function setupScrollReveal() {
        if (!observer && typeof IntersectionObserver !== 'undefined') {
            const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
            observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
        }

        document.querySelectorAll('.reveal').forEach((el) => {
            el.classList.remove('active');
            observer?.observe(el);
        });
    }

    onMounted(() => {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            toggleDarkMode(true);
        }
        setupScrollReveal();
    });

    // --- FUNCIONES ---
    function toggleDarkMode(force = null) {
        isDarkMode.value = force !== null ? force : !isDarkMode.value;
        if (isDarkMode.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function navigateTo(view) {
        if (currentView.value === view) return;
        window.scrollTo({ top: 0, behavior: 'instant' });
        isTransitioning.value = true; 
        setTimeout(() => {
            currentView.value = view;
            isTransitioning.value = false; 
            nextTick(() => setupScrollReveal());
        }, 300); 
    }

    function navigateToSection(sectionId) {
        if (currentView.value !== 'inicio') {
            navigateTo('inicio');
            setTimeout(() => {
                document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth' });
            }, 350);
        } else {
            document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function handleFormSubmit() {
        modalMessage.value = "Gracias por registrarte. Hemos enviado los detalles de acceso a tu correo.";
        isModalOpen.value = true;
        if (leadForm.value) {
            leadForm.value.reset(); 
        }
    }

    function closeModal() {
        isModalOpen.value = false;
    }
    </script>

    <template>
        <Head title="DominioMatic | Soluciones IT para PyMEs" />

        <div class="min-h-screen bg-white dark:bg-[#0a0a0c] text-[#1d1d1f] dark:text-gray-100 selection:bg-cyan-100 dark:selection:bg-cyan-900 antialiased font-sans transition-colors duration-500">
            
            <nav class="fixed top-0 w-full z-50 transition-all duration-300 border-b border-gray-200/50 dark:border-white/5 bg-white/70 dark:bg-[#0a0a0c]/80 backdrop-blur-lg">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        
                        <div class="flex-shrink-0 flex items-center cursor-pointer" @click="navigateTo('inicio')">
                            <img src="/images/dominiomatic_logo.png" alt="DominioMatic" class="h-8 w-auto">
                            <span class="font-semibold tracking-tight text-sm hidden sm:block ml-3 dark:text-white"></span>
                        </div>

                        
                        <div class="hidden md:flex space-x-8 text-sm font-medium">
                            <button @click="navigateTo('inicio')" :class="currentView === 'inicio' ? 'text-cyan-600 dark:text-cyan-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white'" class="transition-colors">Inicio</button>
                            <button @click="navigateTo('producto')" :class="currentView === 'producto' ? 'text-cyan-600 dark:text-cyan-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white'" class="transition-colors">Producto</button>
                            <button @click="navigateToSection('nosotros')" class="text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors">Nosotros</button>
                            <button @click="navigateToSection('pricing')" class="text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors">Precios</button>
                            <button @click="navigateToSection('contacto')" class="text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors">Contacto</button>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button @click="toggleDarkMode(null)" class="p-2 rounded-full text-gray-500 hover:bg-gray-200/50 dark:hover:bg-gray-800 transition">
                                <svg v-if="!isDarkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                <svg v-else class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </button>

                            <nav v-if="canLogin" class="hidden sm:flex items-center space-x-3">
                                <template v-if="!$page.props.auth.user">
                                    <Link :href="route('login')" class="text-xs font-medium text-gray-500 dark:text-gray-300 hover:text-black dark:hover:text-white px-3 py-2 transition">Ingresar</Link>
                                    <Link v-if="canRegister" :href="route('register')" class="text-xs font-medium bg-[#0066cc] hover:bg-[#0044bb] text-white px-4 py-2 rounded-full transition shadow-sm">Registrarse</Link>
                                </template>
                                <Link v-else :href="route('dashboard')" class="text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 transition-all">Panel de Control</Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </nav>

            <div :class="{'opacity-0': isTransitioning}" class="transition-opacity duration-300">
                
                <div v-if="currentView === 'inicio'" id="view-inicio" class="page-view">
                    
                    <header class="relative w-full min-h-[90vh] flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-black pt-16">
                        <div class="absolute inset-0 z-0">
                            <img src="/images/banner_principal.webp" alt="Infraestructura" class="w-full h-full object-cover opacity-60 dark:opacity-40">
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-b from-white/5 via-white/10 to-white dark:from-transparent dark:to-[#0a0a0c] z-10"></div>

                        <div class="relative z-20 text-center px-4 max-w-4xl mx-auto -mt-10">
                            <h1 class="text-5xl md:text-7xl font-bold tracking-tighter mb-6 text-apple-glow leading-tight">
                                Tecnología que impulsa <br/> tu empresa.
                            </h1>
                            <p class="text-xl md:text-2xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto mb-10 font-light">
                                Servicios integrales de IT, dominios y desarrollo de software a medida. Diseñado en Bolivia para el éxito global de tu empresa.
                            </p>
                            <div class="flex gap-4 justify-center items-center">
                                <button @click="navigateToSection('contacto')" class="apple-glow-btn px-10 py-4 rounded-full text-lg font-medium shadow-lg">
                                    Inicia tu prueba gratis
                                </button>
                            </div>
                        </div>
                    </header>

                    <section class="py-10 px-4 max-w-5xl mx-auto text-center reveal -mt-24 relative z-30">
                        <div class="w-full rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transform transition-transform hover:scale-[1.01] duration-500">
                            <img src="/images/mockup_macbook.png" alt="Plataforma DominioMatic" class="w-full h-auto object-cover">
                        </div>
                    </section>

                    <section id="nosotros" class="py-24 bg-gray-50 dark:bg-[#0a0a0c] reveal">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <div class="grid lg:grid-cols-2 gap-12 items-center">
                                <div>
                                    <span class="text-apple-glow font-bold uppercase tracking-wider text-sm mb-3 block">
                                        Nosotros
                                    </span>
                                    <h2 class="text-4xl md:text-5xl font-bold tracking-tight text-gray-900 dark:text-white mb-6">
                                        Somos especialistas en marcas online, diseño y desarrollo web.
                                    </h2>
                                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-5">
                                        Comenzamos como especialistas en Diseño y Desarrollo de Sitios Web, además de Web & Mail Hosting.
                                        Con el tiempo ampliamos nuestros servicios hacia dos áreas que complementan perfectamente esa base:
                                        Gestión de Redes Sociales y Diseño Gráfico Corporativo.
                                    </p>
                                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-5">
                                        Desde inicios de 2001, cuando atendimos a nuestro primer cliente en Estados Unidos, empezamos a formar
                                        una trayectoria marcada por estándares internacionales de calidad, competitividad, rapidez y puntualidad.
                                    </p>
                                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                                        Con los años trabajamos con clientes de distintas partes del mundo, y esa experiencia consolidó
                                        a DominioMatic.com como una empresa especialista en Manejo y Creación de Marcas Online.
                                    </p>
                                </div>

                                <div class="grid gap-5">
                                    <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Desde 2001</h3>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            Una trayectoria construida con experiencia real y proyectos para clientes de diferentes países.
                                        </p>
                                    </div>

                                    <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Enfoque integral</h3>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            Desarrollo web, hosting, redes sociales y diseño gráfico bajo una misma visión de marca.
                                        </p>
                                    </div>

                                    <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Marca online sólida</h3>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            Soluciones pensadas para ayudar a empresas a verse profesionales y crecer con presencia digital.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="servicios" class="py-24 bg-white dark:bg-[#0a0a0c] reveal">
                        <div class="max-w-7xl mx-auto px-4 text-center">
                            <span class="text-apple-glow font-bold uppercase tracking-wider text-sm mb-2 block">Problema / Solución</span>
                            <h2 class="text-4xl font-bold tracking-tight mb-4 text-gray-900 dark:text-white">El caos de IT, resuelto.</h2>
                            <p class="text-xl text-gray-500 dark:text-gray-400 mb-16 max-w-2xl mx-auto">Deja atrás las hojas de cálculo y vencimientos sorpresas. Administra toda tu infraestructura en un ecosistema centralizado.</p>
                            
                            <div class="grid md:grid-cols-3 gap-8 text-left">
                                <div class="bg-gray-50 dark:bg-gray-900 p-8 rounded-3xl transition-all duration-300 border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-[#0066cc] dark:text-blue-400 rounded-2xl flex items-center justify-center mb-6">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-bold mb-3 dark:text-white">Gestión de Dominios</h3>
                                    <p class="text-gray-500 dark:text-gray-400">Motor inteligente de suscripciones con alertas tempranas. Nunca vuelvas a perder un dominio.</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900 p-8 rounded-3xl transition-all duration-300 border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-[#c600bf] dark:text-purple-400 rounded-2xl flex items-center justify-center mb-6">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-bold mb-3 dark:text-white">Multi-Tenant Aislado</h3>
                                    <p class="text-gray-500 dark:text-gray-400">Aislamiento total de base de datos. Administra decenas de empresas inquilinas desde un solo panel.</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900 p-8 rounded-3xl transition-all duration-300 border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                                    <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900/30 text-[#ff2d55] dark:text-pink-400 rounded-2xl flex items-center justify-center mb-6">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-bold mb-3 dark:text-white">Atributos EAV</h3>
                                    <p class="text-gray-500 dark:text-gray-400">Guarda credenciales FTP, IPs o DNS de forma dinámica, adaptándose a requerimientos técnicos.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="pricing" class="py-24 bg-gray-50 dark:bg-gray-900/50 reveal border-t border-gray-100 dark:border-white/5">
                        <div class="max-w-7xl mx-auto px-4 text-center">
                            <h2 class="text-4xl font-bold tracking-tight mb-4 dark:text-white">Planes a tu medida</h2>
                            <p class="text-xl text-gray-500 dark:text-gray-400 mb-16">Escala tu infraestructura técnica con nosotros.</p>
                            
<div class="grid lg:grid-cols-3 gap-8 max-w-7xl mx-auto">

    <!-- FREEMIUM -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-700 text-left relative">
        <h3 class="text-2xl font-bold dark:text-white mb-2">
            Freemium Beta
        </h3>

        <p class="text-gray-500 dark:text-gray-400 mb-6">
            Ideal para probar la plataforma antes de una implementación empresarial.
        </p>

        <div class="text-4xl font-bold dark:text-white mb-6">
            $0
            <span class="text-lg text-gray-400 font-normal">/mes</span>
        </div>

        <ul class="space-y-4 mb-8 text-gray-600 dark:text-gray-300">
            <li class="flex items-center gap-2"><span>✓</span> Acceso al entorno Beta</li>
            <li class="flex items-center gap-2"><span>✓</span> Base de datos compartida</li>
            <li class="flex items-center gap-2"><span>✓</span> Interfaz estándar DominioMatic</li>
            <li class="flex items-center gap-2"><span>✓</span> Gestión básica de clientes</li>
            <li class="flex items-center gap-2"><span>✓</span> Actualizaciones de prueba</li>
            <li class="flex items-center gap-2"><span>✓</span> Soporte por correo electrónico</li>
        </ul>

        <button
            @click="navigateToSection('contacto')"
            class="w-full py-3 rounded-full font-medium border-2 border-gray-900 dark:border-white text-gray-900 dark:text-white hover:bg-gray-900 hover:text-white dark:hover:bg-white dark:hover:text-black transition">
            Probar Gratis
        </button>
    </div>

    <!-- STANDARD -->
    <div class="bg-gray-900 dark:bg-black p-8 rounded-3xl shadow-2xl border border-gray-800 text-left relative overflow-hidden">

        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-[#0066cc] to-[#c600bf] rounded-full mix-blend-screen filter blur-3xl opacity-50"></div>

        <span class="bg-gradient-to-r from-[#0066cc] to-[#00b3ff] text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide absolute top-6 right-6">
            Empresas
        </span>

        <h3 class="text-2xl font-bold text-white mb-2 relative z-10">
            Standard Enterprise
        </h3>

        <p class="text-gray-400 mb-6 relative z-10">
            Plataforma profesional lista para producción.
        </p>

        <div class="text-4xl font-bold text-white mb-6 relative z-10">
            $19.99
            <span class="text-lg text-gray-500 font-normal">/mes</span>
        </div>

        <ul class="space-y-4 mb-8 text-gray-300 relative z-10">
            <li class="flex items-center gap-2"><span>✓</span> Todo lo incluido en Freemium</li>
            <li class="flex items-center gap-2"><span>✓</span> 1 usuario administrador incluido</li>
            <li class="flex items-center gap-2"><span>✓</span> Facturas personalizables</li>
            <li class="flex items-center gap-2"><span>✓</span> Base de datos dedicada</li>
            <li class="flex items-center gap-2"><span>✓</span> Personalización básica de interfaz</li>
            <li class="flex items-center gap-2"><span>✓</span> Backups automáticos</li>
            <li class="flex items-center gap-2"><span>✓</span> Soporte técnico</li>
        </ul>

        <button
            @click="navigateToSection('contacto')"
            class="w-full py-3 rounded-full font-medium apple-glow-btn text-white transition relative z-10">
            Solicitar Implementación
        </button>
    </div>

    <!-- PRO ANUAL -->
    <div class="bg-gradient-to-br from-[#0f172a] via-[#1e1b4b] to-[#170f1c] p-8 rounded-3xl shadow-2xl border border-fuchsia-500/30 text-left relative overflow-hidden">

        <span class="bg-gradient-to-r from-[#c600bf] to-[#ff2d55] text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide absolute top-6 right-6">
            Premium
        </span>

        <h3 class="text-2xl font-bold text-white mb-2">
            Pro Enterprise Annual
        </h3>

        <p class="text-gray-300 mb-6">
            Solución corporativa completamente personalizada.
        </p>

        <div class="text-4xl font-bold text-white mb-6">
            $199.99
            <span class="text-lg text-gray-400 font-normal">/año</span>
        </div>

        <ul class="space-y-4 mb-8 text-gray-200">
            <li class="flex items-center gap-2"><span>✓</span> Todo lo incluido en Standard</li>
            <li class="flex items-center gap-2"><span>✓</span> Diseño visual personalizado</li>
            <li class="flex items-center gap-2"><span>✓</span> Plataforma adaptada al rubro</li>
            <li class="flex items-center gap-2"><span>✓</span> Base de datos exclusiva optimizada</li>
            <li class="flex items-center gap-2"><span>✓</span> Integración de procesos internos</li>
            <li class="flex items-center gap-2"><span>✓</span> Soporte técnico dedicado</li>
            <li class="flex items-center gap-2"><span>✓</span> Consultoría especializada</li>
            <li class="flex items-center gap-2"><span>✓</span> Mantenimiento premium</li>
        </ul>

        <button
            @click="navigateToSection('contacto')"
            class="w-full py-3 rounded-full font-medium bg-white text-black hover:bg-gray-200 transition">
            Solicitar Cotización
        </button>
    </div>

</div>
                        </div>
                    </section>

                    <section class="py-24 bg-white dark:bg-[#0a0a0c] reveal">
                        <div class="max-w-7xl mx-auto px-4 text-center">
                            <h2 class="text-3xl font-bold tracking-tight mb-12 dark:text-white">Empresas que confían en nosotros</h2>
                            <div class="grid md:grid-cols-3 gap-6">
                                <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-2xl text-left border border-gray-100 dark:border-gray-800">
                                    <p class="text-gray-600 dark:text-gray-300 italic mb-4">"DominioMatic nos ahorró cientos de horas organizando nuestros servidores. La función Multi-Tenant es simplemente perfecta."</p>
                                    <div class="font-bold dark:text-white">Carlos R.</div>
                                    <div class="text-sm text-gray-500">CTO, TechBolivia</div>
                                </div>
                                <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-2xl text-left border border-gray-100 dark:border-gray-800">
                                    <p class="text-gray-600 dark:text-gray-300 italic mb-4">"Perdíamos dominios por falta de alertas. Desde que integramos este SaaS, tenemos el 100% de la infraestructura controlada."</p>
                                    <div class="font-bold dark:text-white">María F.</div>
                                    <div class="text-sm text-gray-500">Agencia Creativa</div>
                                </div>
                                <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-2xl text-left border border-gray-100 dark:border-gray-800">
                                    <p class="text-gray-600 dark:text-gray-300 italic mb-4">"Los atributos EAV nos permiten personalizar las credenciales por cliente sin tocar la base de datos. Una obra de ingeniería."</p>
                                    <div class="font-bold dark:text-white">José L.</div>
                                    <div class="text-sm text-gray-500">SysAdmin Independiente</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="contacto" class="py-24 bg-gray-50 dark:bg-gray-950 reveal">
                        <div class="max-w-3xl mx-auto px-4">
                            <div class="bg-gray-900 dark:bg-[#0a0a0c] rounded-[2.5rem] p-10 md:p-16 text-center text-white shadow-2xl overflow-hidden relative border border-gray-800/50">
                                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-[#0066cc]/20 via-[#c600bf]/20 to-[#ff2d55]/20 mix-blend-screen"></div>
                                
                                <h2 class="text-3xl md:text-4xl font-bold mb-4 relative z-10">Optimiza tu PyME hoy</h2>
                                <p class="text-gray-300 mb-8 relative z-10 text-lg">Regístrate para obtener una demostración o iniciar tu prueba gratis.</p>
                                
                                <form @submit.prevent="handleFormSubmit" ref="leadForm" class="relative z-10 flex flex-col md:flex-row gap-2">
                                    <input type="text" placeholder="Nombre completo" required class="flex-1 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-6 py-4 text-white placeholder-gray-400 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all">
                                    <input type="email" placeholder="Tu correo electrónico" required class="flex-1 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-6 py-4 text-white placeholder-gray-400 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all">
                                    <button type="submit" class="apple-glow-btn px-8 py-4 rounded-2xl font-bold transition-all whitespace-nowrap">
                                        Quiero mi Demo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>

                <div v-else-if="currentView === 'producto'" class="page-view pt-24 min-h-screen bg-gray-50 dark:bg-[#0a0a0c]">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-10 text-center">
            <span class="inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold tracking-widest uppercase bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                Documentación técnica
            </span>
            <h1 class="mt-4 text-4xl md:text-5xl font-bold tracking-tight text-gray-900 dark:text-white">
                Cómo funciona DominioMatic PWA
            </h1>
            <p class="mt-4 max-w-3xl mx-auto text-lg text-gray-600 dark:text-gray-300">
                Esta sección explica la arquitectura del sistema, el modelo multi-inquilino, el patrón EAV y el mapa de navegación del SaaS.
            </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-12">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Arquitectura base</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    DominioMatic está construido con Laravel, Inertia.js y Vue 3. Laravel maneja autenticación, rutas y backend; Inertia conecta el frontend con el backend sin crear una API REST separada.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Multi-Tenant</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Cada cliente o empresa tiene su propio espacio lógico aislado. Eso permite administrar múltiples empresas desde un solo panel sin mezclar datos críticos.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Patrón EAV</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    El modelo Entity-Attribute-Value permite guardar datos dinámicos como FTP, DNS, IPs o credenciales personalizadas sin cambiar el esquema de la base de datos cada vez.
                </p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-[2rem] p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-5">1. Arquitectura del SaaS</h2>
                <div class="space-y-4 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    <p>
                        El proyecto usa un enfoque monolítico moderno, con Laravel como núcleo del backend y Vue 3 como interfaz reactiva. Inertia.js actúa como puente entre ambos.
                    </p>
                    <p>
                        Esto permite que la experiencia se comporte como una SPA, pero manteniendo la simplicidad de una aplicación Laravel tradicional.
                    </p>
                    <p>
                        La ventaja principal es que no se necesita montar una API separada solo para el frontend.
                    </p>
                </div>

                <div class="mt-6 rounded-2xl bg-gray-50 dark:bg-gray-800 p-4 text-sm font-mono text-gray-700 dark:text-gray-200 overflow-x-auto">
                    Laravel → Inertia → Vue 3 → Interfaz SPA
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-[2rem] p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-5">2. Problema / Solución</h2>
                <div class="space-y-4 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    <p>
                        Muchas PyMEs administran dominios, hostings y credenciales en hojas de cálculo o chats internos. Eso genera olvidos, pérdida de información y errores de renovación.
                    </p>
                    <p>
                        DominioMatic centraliza toda esa información en una sola plataforma con alertas, paneles organizados y estructura pensada para equipos de TI.
                    </p>
                    <p>
                        Así se reduce el caos operativo y se mejora el control sobre recursos críticos de infraestructura.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-[2rem] p-8 shadow-sm mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">3. Funcionalidades principales</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 p-5 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Gestión inteligente de dominios</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        Registro, seguimiento y alertas para evitar pérdidas por vencimiento o descuidos administrativos.
                    </p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 p-5 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Aislamiento por cliente</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        Cada tenant mantiene su información separada para una administración clara y segura.
                    </p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 p-5 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Atributos dinámicos EAV</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        Permite guardar campos personalizados según el tipo de servicio o cliente sin alterar tablas principales.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-[2rem] p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-5">4. Mapa de navegación del sistema SaaS</h2>
                <div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Landing Page</p>
                        <p>Presentación comercial del sistema con llamada a la acción, precios, testimonios y formulario.</p>
                    </div>
                    <div class="flex items-center justify-center text-cyan-500 text-xl">↓</div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Producto / Documentación técnica</p>
                        <p>Explicación interna de arquitectura, modelo multi-tenant y motor EAV.</p>
                    </div>
                    <div class="flex items-center justify-center text-cyan-500 text-xl">↓</div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Registro / Inicio de sesión</p>
                        <p>Entrada del usuario al ecosistema del SaaS.</p>
                    </div>
                    <div class="flex items-center justify-center text-cyan-500 text-xl">↓</div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">Dashboard principal</p>
                        <p>Administración de dominios, tenants, servicios y atributos personalizados.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-[2rem] p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-5">5. Ejemplo técnico de uso</h2>
                <div class="space-y-4 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white mb-2">Respuesta simulada</p>
                        <pre class="overflow-x-auto text-xs font-mono">{
"tenant": "empresa-1",
"domains": 12,
"alerts": ["Renovación pendiente", "DNS actualizado"]
}</pre>
                    </div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                        <p class="font-semibold text-gray-900 dark:text-white mb-2">Ventaja del modelo</p>
                        <p>
                            El sistema permite crecer sin reescribir la base de datos cada vez que un cliente necesite un nuevo campo técnico.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-cyan-600 to-fuchsia-600 rounded-[2rem] p-8 md:p-10 text-white shadow-2xl">
            <h2 class="text-2xl md:text-3xl font-bold mb-3">6. Resumen de valor</h2>
            <p class="max-w-4xl text-white/90 leading-relaxed">
                DominioMatic PWA resuelve el caos operativo de infraestructura IT, centraliza datos críticos, separa la información por empresa y permite una administración flexible y escalable.
            </p>
        </div>

        <div class="mt-10 text-center">
            <button @click="navigateTo('inicio')" class="apple-glow-btn px-8 py-4 rounded-full font-semibold">
                Volver al inicio
            </button>
        </div>
    </section>
</div>

            </div>

            <footer class="py-12 border-t border-gray-200 dark:border-white/10 bg-white dark:bg-[#0a0a0c] text-sm text-gray-500 dark:text-gray-400 transition-colors">
                <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p>&copy; 2026 DominioMatic.com. Todos los derechos reservados.</p>
                    <div class="flex space-x-6">
                        <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Términos de Servicio</a>
                        <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Política de Privacidad</a>
                    </div>
                    <p class="text-xs">
                        Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                    </p>
                </div>
            </footer>

            <div v-if="isModalOpen" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[60] transition-opacity duration-300">
                <div class="bg-white dark:bg-gray-900 p-8 rounded-3xl max-w-sm w-full text-center shadow-2xl border border-gray-100 dark:border-gray-800">
                    <img src="/images/mockup_macbook.png" alt="Registro Exitoso" class="w-full h-32 object-cover rounded-xl mb-6 shadow-sm border border-gray-100 dark:border-gray-800">
                    
                    <h3 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">¡Éxito!</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 text-sm">{{ modalMessage }}</p>
                    <button @click="closeModal" class="w-full bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-semibold py-3 rounded-2xl transition-transform active:scale-95">
                        Comenzar
                    </button>
                </div>
            </div>

        </div>
    </template>

    <style>
    /* Base de transición de tema */
    html.dark {
        color-scheme: dark;
    }

    /* Animaciones */
    .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
    .reveal.active { opacity: 1; transform: translateY(0); }
    .page-view { transition: opacity 0.4s ease-in-out; }

    /* Gradiente Apple Intelligence */
    .text-apple-glow {
        background: linear-gradient(135deg, #0066cc 0%, #c600bf 50%, #ff2d55 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Botón Glow Apple Mejorado */
    .apple-glow-btn {
        position: relative;
        /* Un azul/pizarra muy oscuro y premium para modo Light (en lugar de negro puro) */
        background-color: #0f172a; 
        color: #ffffff;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .dark .apple-glow-btn {
        /* Un tono sutilmente índigo muy oscuro para que cuadre con la gama en modo Dark */
        background-color: #170f1c; 
        color: #ffffff;
    }
    .apple-glow-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(198, 0, 191, 0.3);
    }
    .apple-glow-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        padding: 1.5px;
        border-radius: inherit;
        background: linear-gradient(135deg, #0066cc, #c600bf, #ff2d55);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.8;
    }
    </style>