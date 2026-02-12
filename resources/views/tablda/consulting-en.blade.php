<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TablDA Consulting | Practical Tools. Smart Automation. Real Results.</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('assets/css/vendor.css') }}">
    <style>
        html { scroll-behavior: smooth; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        @media (max-width: 1440px) and (min-width: 1024px) {
            .consulting-nav {
                margin-left: 160px !important;
                margin-right: 115px !important;
            }
        }

        .dropdown {
            position: absolute;
            right: 0;
            top: 0;
            color: #777;
        }

        .dropdown-toggle {
            white-space: nowrap;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }

        .dropdown-toggle:empty::after {
            margin-left: 0;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            float: left;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 1rem;
            color: #333;
            text-align: left;
            list-style: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 0.25rem;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.25rem 1.5rem;
            clear: both;
            font-weight: 400;
            color: #333;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
        }

        .dropdown-item:hover, .dropdown-item:focus {
            color: #222;
            text-decoration: none;
            background-color: #f8f9fa;
        }

        .dropdown-item.active, .dropdown-item:active {
            color: #fff;
            text-decoration: none;
            background-color: #007bff;
        }

        .dropdown-item.disabled, .dropdown-item:disabled {
            color: #777;
            pointer-events: none;
            background-color: transparent;
        }

        .dropdown-menu.show {
            display: block;
        }
    </style>
</head>

<body>
    <div id="app" class="font-sans text-gray-800">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full bg-white shadow z-50 pt-14 lg:pt-0">
            <a href="/" style="position: absolute; top: 7px; left: 7px;">
                <img src="/assets/img/TablDA_w_text.png" height="50" alt="TablDA" style="height: 50px;">
            </a>
            <a href="/consulting/cn" class="absolute text-blue-400 hover:text-blue-600" style="top: 20px; left: 135px;">中文</a>
            <div class="max-w-7xl mx-auto flex flex-wrap justify-center md:justify-between items-center py-4 px-2 lg:px-4 consulting-nav">
                <h1 class="text-2xl font-bold text-blue-700 text-center">TablDA Consulting</h1>
                <ul class="flex space-x-6 text-sm font-medium ml-2 text-center">
                    <!-- li><a href="#home" class="hover:text-blue-600">Home</a></li -->
                    <li><a href="#offerings" class="hover:text-blue-600">What We Offer</a></li>
                    <li><a href="#cases" class="hover:text-blue-600">Case Studies</a></li>
                    <li><a href="#team" class="hover:text-blue-600">About Us</a></li>
                    <!-- li><a href="#contact" class="hover:text-blue-600">30-Min. Free Consultation</a></li -->
                    <li><a target="_blank" rel="noopener noreferrer" href="https://tablda.com/dcr/86f705d3-a861-4d7c-903f-0e13111b0e12" class="hover:text-blue-600">30-Min. Free Consultation</a></li>
                </ul>
            </div>
            @if (Auth::guard()->user())
                <div v-if="!$root.user.see_view" class="dropdown">
                    <a href="#" class="dropdown-toggle flex items-center p-3" data-toggle="dropdown">
                        <img src="{{ auth()->user()->present()->avatar }}" style="width: 40px; margin-right: 4px; border: 2px solid #ccc; padding: 2px; border-radius: 50%;">
                        {{ Auth::user()->present()->name }}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu text-sm">
                        <li class="dropdown-item">
                            <a href="{{ route('profile') }}" target="_blank">
                                <i class="fas fa-user text-muted"></i>
                                @lang('app.my_profile')
                            </a>
                        </li>
                        @if (config('session.driver') == 'database')
                            <li class="dropdown-item">
                                <a href="{{ route('profile.sessions') }}" target="_blank">
                                    <i class="fa fa-list"></i>
                                    @lang('app.active_sessions')
                                </a>
                            </li>
                        @endif
                        <li class="dropdown-item">
                            <a href="/data/?subscription" target="_blank">
                                <i class="fas fa-server"></i>
                                <span>Subscription</span>
                            </a>
                        </li>
                        <li class="dropdown-item">
                            <a href="/data/?settings" target="_blank">
                                <i class="fas fa-cogs"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        <li class="dropdown-item">
                            <a href="/data/?invites" target="_blank">
                                <i class="fas fa-share"></i>
                                <span>Tell Friends</span>
                            </a>
                        </li>
                        @if (auth()->user()->subdomain && auth()->user()->canEditStatic())
                            <li class="dropdown-item">
                                <a href="{{ auth()->user()->_available_features->apps_are_avail ? route('apps') : 'javascript:void(0)' }}"
                                   target="{{ auth()->user()->_available_features->apps_are_avail ? '_blank' : '' }}"
                                   class="{{ auth()->user()->_available_features->apps_are_avail ? '' : 'disabled' }}"
                                >
                                    <i class="fas fa-tablet-alt"></i>
                                    <span>Apps</span>
                                </a>
                            </li>
                        @endif
                        <li class="dropdown-item">
                            <a href="{{ route('auth.logout') }}">
                                <i class="fa fa-sign-out"></i>
                                @lang('app.logout')
                            </a>
                        </li>
                    </ul>
                </div>
            @else
                <div class="dropdown flex items-center py-4">
                    <a style="padding-right: 0;" class="dropdown-item" href="/data/?login" target="_blank">
                        <i class="fas fa-sign-in-alt text-muted mr-2"></i>
                        @lang('app.login')
                    </a>
                    <a style="padding-left: 0;" class="dropdown-item" href="/data/?register" target="_blank">
                        <span> / </span>@lang('app.register')
                    </a>
                </div>
            @endif
        </nav>

        <!-- Hero -->
        <section id="home" class="pt-52 md:pt-40 lg:pt-28 pb-16 bg-gradient-to-b from-blue-50 to-white text-center">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-4">
                Practical Solutions. Smart Automation. Real Results.
            </h2>
            <p class="max-w-3xl mx-auto text-lg mb-6">
                At <strong>TablDA Consulting</strong>, we design and deliver efficient, intuitive, and reliable solutions that help teams and organizations work smarter. We simplify data management — from collection and transformation to sharing — eliminate repetitive manual tasks, automate workflows, and boost productivity, all without the complexity of large enterprise systems.
            </p>
            <p class="max-w-2xl mx-auto text-lg text-gray-700">
                Whether you need a powerful Excel/VBA automation, a collaborative Google Sheets app, a standalone desktop tool, or a cloud-based platform built with Airtable, Notion, Monday.com, TablDA, or AI-driven solutions — we create tools people actually enjoy using.
            </p>
        </section>

        <!-- What We Offer -->
        <section id="offerings" class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <h3 class="text-3xl font-bold text-center mb-10 text-blue-700">What We Offer</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 text-gray-700">
                    <div>
                        <h4 class="font-semibold text-lg mb-2">Excel / Access / VBA Automation</h4>
                        <p>Advanced tools for automation, dashboards, and reporting tailored to your needs and workflow.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg mb-2">Google Sheets + Apps Script</h4>
                        <p>Collaborative online tools powered by JavaScript and cloud APIs.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg mb-2">AI-Powered Python Applications</h4>
                        <p>Data collection, analytics, and process automation using AI and machine learning.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg mb-2">No-Code / Low-Code Platforms</h4>
                        <p>Rapid application building with Airtable, Notion, Monday.com, or TablDA.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg mb-2">Cloud-Integrated Systems</h4>
                        <p>Flexible, connected solutions enabling real-time collaboration.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg mb-2">Custom Integrations</h4>
                        <p>Bridging Salesforce, Netsuite, databases, and third-party APIs into unified workflows.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Case Studies (Horizontal Slider) -->
        <section id="cases" class="py-16 bg-blue-50">
            <div class="max-w-6xl mx-auto px-6">
                <h3 class="text-3xl font-bold text-center mb-10 text-blue-700">
                    Sample Projects & Case Studies
                </h3>

                <div class="relative">
                    <!-- Left Arrow -->
                    <button id="btn-left" type="button"
                            class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 z-10
                         h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow
                         hover:bg-white focus:outline-none">‹</button>

                    <!-- Right Arrow -->
                    <button id="btn-right" type="button"
                            class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 z-10
                         h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow
                         hover:bg-white focus:outline-none">›</button>

                    <!-- Edge fade hints -->
                    <div class="pointer-events-none absolute inset-y-0 left-0 w-10 bg-gradient-to-r from-blue-50 to-transparent"></div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-gradient-to-l from-blue-50 to-transparent"></div>

                    <!-- Slider Track -->
                    <div id="caseTrack"
                         class="flex gap-6 overflow-x-auto no-scrollbar scroll-px-6 px-1 snap-x snap-mandatory"
                         style="scroll-behavior:smooth;">
                        <!-- Cards -->
                        <article class="snap-start shrink-0 w-[22rem] bg-white rounded-xl shadow p-6">
                            <h4 class="text-lg font-semibold mb-2 text-gray-900">Excel/VBA Automation for Engineering Workflow</h4>
                            <p class="text-gray-700 text-sm">
                                <strong>Challenge:</strong> Manual consolidation took hours.<br>
                                <strong>Solution:</strong> Excel/VBA-based automations.<br>
                                <strong>Impact:</strong> Hours to minutes.
                            </p>
                        </article>

                        <article class="snap-start shrink-0 w-[22rem] bg-white rounded-xl shadow p-6">
                            <h4 class="text-lg font-semibold mb-2 text-gray-900">Salesforce Integration with Excel</h4>
                            <p class="text-gray-700 text-sm">
                                <strong>Challenge:</strong> Inefficient manual sync. Limited flexibility.<br>
                                <strong>Solution:</strong> VBA connector via Salesforce API.<br>
                                <strong>Impact:</strong> Much faster data CRUD & better accuracy.
                            </p>
                        </article>

                        <article class="snap-start shrink-0 w-[22rem] bg-white rounded-xl shadow p-6">
                            <h4 class="text-lg font-semibold mb-2 text-gray-900">Python-Based Data Processing</h4>
                            <p class="text-gray-700 text-sm">
                                <strong>Challenge:</strong> Repetitive manual analysis using Excel.<br>
                                <strong>Solution:</strong> Python batch automation tool.<br>
                                <strong>Impact:</strong> Saved a full workday per cycle.
                            </p>
                        </article>

                        <article class="snap-start shrink-0 w-[22rem] bg-white rounded-xl shadow p-6">
                            <h4 class="text-lg font-semibold mb-2 text-gray-900">Engineering Simulation Automation</h4>
                            <p class="text-gray-700 text-sm">
                                <strong>Challenge:</strong> Manual/GUI-based creation of finite element models using different software including RISA 3D, GT Strudl, ANSYS, etc.<br>
                                <strong>Solution:</strong>Utilities for model conversions<br>
                                <strong>Impact:</strong> Hours → Minutes
                            </p>
                        </article>

                        <article class="snap-start shrink-0 w-[22rem] bg-white rounded-xl shadow p-6">
                            <h4 class="text-lg font-semibold mb-2 text-gray-900">Cloud Collaboration Apps</h4>
                            <p class="text-gray-700 text-sm">
                                <strong>Challenge:</strong> Scattered project data.<br>
                                <strong>Solution:</strong> Airtable/TablDA workflow system.<br>
                                <strong>Impact:</strong> Unified data, better accountability.
                            </p>
                        </article>

                        <article class="snap-start shrink-0 w-[22rem] bg-white rounded-xl shadow p-6">
                            <h4 class="text-lg font-semibold mb-2 text-gray-900">AI-Enabled Data Quality Monitoring</h4>
                            <p class="text-gray-700 text-sm">
                                <strong>Challenge:</strong> Inconsistent field data.<br>
                                <strong>Solution:</strong> AI anomaly detection & alerts.<br>
                                <strong>Impact:</strong> Higher reliability, less rework.
                            </p>
                        </article>
                    </div>

                </div>
            </div>
        </section>

        <!-- About the Team -->
        <section id="team" class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <h3 class="text-3xl font-bold text-center mb-10 text-blue-700">About Us</h3>
                <p class="max-w-4xl mx-auto text-gray-700 text-lg leading-relaxed">
                    <strong>TablDA Consulting</strong> brings together professionals with diverse backgrounds —
                    experienced engineers, skilled software developers, and AI experts.
                    Led by a founder with <strong>20+ years of engineering experience</strong>,
                    we bridge the gap between technical depth and usability.
                </p>
                <p class="max-w-4xl mx-auto text-gray-700 text-lg leading-relaxed mt-4">
                    We build solutions that people <strong>love to use</strong> — intuitive, reliable, and tailored to
                    each client’s workflow. From startups to established businesses, we deliver tools that improve
                    efficiency, accuracy, and collaboration.
                </p>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact" class="py-16 bg-blue-700 text-white text-center">
            <h3 class="text-3xl font-bold mb-6">Let’s Build Something That Works</h3>
            <p class="max-w-2xl mx-auto text-lg mb-8">
                Ready to automate tasks or streamline workflows?
                <br/>Reach out today for a 30-min. free consultation.
            </p>
            <a target="_blank" rel="noopener noreferrer" href="https://tablda.com/dcr/86f705d3-a861-4d7c-903f-0e13111b0e12"
               class="bg-white text-blue-700 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100">
                Contact Us
            </a>
        </section>

        <footer class="bg-gray-100 text-center py-4 text-sm text-gray-600">
            © <span id="year"></span> TablDA Consulting. All rights reserved.
        </footer>
    </div>

    <script src="{{ mix('assets/js/tablda/vendor.js') }}"></script>
    <script src="{{ url('assets/js/vue-virtual-scroller.min.js') }}"></script>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();

        function stepSize() {
            const card = window.csTrack.querySelector('article');
            if (!card) return 300;
            const styles = getComputedStyle(window.csTrack);
            const gap = parseInt(styles.columnGap || styles.gap || '24', 10);
            return Math.ceil(card.getBoundingClientRect().width + (isNaN(gap) ? 24 : gap));
        }

        function scrollByStep(dir = 1) {
            window.csTrack.scrollBy({ left: dir * stepSize(), behavior: 'smooth' });
        }

        // Horizontal slider behavior
        $(document).ready(() => {
            window.csTrack = document.getElementById('caseTrack');
            if (!window.csTrack) return;

            // Convert vertical scroll to horizontal
            window.csTrack.addEventListener('wheel', (e) => {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                    e.preventDefault();
                    window.csTrack.scrollBy({ left: e.deltaY, behavior: 'smooth' });
                }
            }, { passive: false });

            const leftBtn  = document.getElementById('btn-left');
            const rightBtn = document.getElementById('btn-right');

            leftBtn && leftBtn.addEventListener('mousedown', () => scrollByStep(-1));
            rightBtn && rightBtn.addEventListener('mousedown', () => scrollByStep(1));

            // Keyboard support
            window.csTrack.setAttribute('tabindex', '0');
            window.csTrack.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') scrollByStep(1);
                if (e.key === 'ArrowLeft')  scrollByStep(-1);
            });
        });
    </script>
</body>
</html>
