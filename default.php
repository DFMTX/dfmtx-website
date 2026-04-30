<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Don't Forget Me Flowers - Gravesite Care</title>
    <link rel="icon" type="image/png" href="favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- Firebase Integration -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getAuth, signInWithCustomToken, signInAnonymously } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import { getFirestore, collection, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        const firebaseConfig = JSON.parse(__firebase_config);
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const db = getFirestore(app);
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'dont-forget-me-flowers';

        const initAuth = async () => {
            try {
                if (typeof __initial_auth_token !== 'undefined' && __initial_auth_token) {
                    await signInWithCustomToken(auth, __initial_auth_token);
                } else {
                    await signInAnonymously(auth);
                }
            } catch (error) {
                console.error("Auth failed:", error);
            }
        };
        initAuth();

        window.submitOrder = async (orderData) => {
            if (!auth.currentUser) {
                const msg = document.getElementById('form-message');
                msg.textContent = "Session expired. Please refresh the page.";
                msg.className = "text-red-500 font-bold mt-4 text-center p-4 bg-red-50 rounded-xl";
                return;
            }

            const btn = document.getElementById('submit-btn');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = "Processing...";

            const executeWithRetry = async (fn, retries = 5, delay = 1000) => {
                for (let i = 0; i < retries; i++) {
                    try { return await fn(); }
                    catch (err) {
                        if (i === retries - 1) throw err;
                        await new Promise(r => setTimeout(r, delay * Math.pow(2, i)));
                    }
                }
            };

            try {
                await executeWithRetry(() => addDoc(
                    collection(db, 'artifacts', appId, 'public', 'data', 'orders'),
                    {
                        ...orderData,
                        userId: auth.currentUser.uid,
                        createdAt: serverTimestamp(),
                        status: 'pending'
                    }
                ));
                
                document.getElementById('intake-form-container').classList.add('hidden');
                document.getElementById('success-message').classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
                const msg = document.getElementById('form-message');
                msg.textContent = "Error saving request. Please check your connection and try again.";
                msg.className = "text-red-500 font-bold mt-4 text-center p-4 bg-red-50 rounded-xl";
                btn.disabled = false;
                btn.textContent = originalText;
            }
        };
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        
        /* Fixed Background logic using irisbg.png */
        .iris-bg {
            background-image: url('images/irisbg.png');
            background-repeat: repeat-y;
            background-size: 100% auto;
            background-position: top center;
        }
        .view-content { display: none; }
        .view-content.active { display: block; }
        
        .custom-radio:checked + label { border-color: #2563eb; background-color: #eff6ff; }
        .custom-radio:checked + label .radio-dot { background-color: #2563eb; border-color: #2563eb; }
        .custom-radio:checked + label .radio-dot-inner { opacity: 1; }
        
        /* Hide scrollbar for step indicators on mobile */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen bg-white selection:bg-blue-100 selection:text-blue-900">

    <nav class="bg-white/95 backdrop-blur-sm py-0.5 px-4 md:px-12 border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <button onclick="switchView('home')" class="flex items-center group py-1 outline-none">
                <div class="relative h-[7rem] md:h-[8.8rem] w-auto flex items-center">
                    <img src="images/DFM LOGO TP.png" alt="Don't Forget Me Flowers" class="h-full w-auto object-contain transition-transform duration-300 group-hover:scale-[1.02]">
                </div>
            </button>
            <div class="hidden lg:flex items-center space-x-10">
                <button onclick="switchView('home')" id="btn-home" class="nav-link text-[11px] font-black tracking-widest text-blue-600 border-b-2 border-blue-600 pb-1 transition-colors uppercase">Home</button>
                <button onclick="switchView('services')" id="btn-services" class="nav-link text-[11px] font-black tracking-widest text-slate-500 hover:text-blue-600 transition-colors uppercase">Services</button>
                <button onclick="switchView('process')" id="btn-process" class="nav-link text-[11px] font-black tracking-widest text-slate-500 hover:text-blue-600 transition-colors uppercase">Our Process</button>
                <button onclick="switchView('about')" id="btn-about" class="nav-link text-[11px] font-black tracking-widest text-slate-500 hover:text-blue-600 transition-colors uppercase">About Us</button>
                <button onclick="switchView('intake')" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md text-[11px] font-black tracking-widest uppercase transition-all shadow-lg active:scale-95 ml-4">
                    Order Placement
                </button>
            </div>
            <button class="lg:hidden p-2 text-slate-600" onclick="toggleMenu()">
                <i id="menu-icon" data-lucide="menu"></i>
            </button>
        </div>
        <div id="mobile-menu" class="hidden lg:hidden absolute top-full left-0 w-full bg-white shadow-xl py-6 px-8 space-y-4 border-t border-gray-100">
            <button onclick="switchView('home'); toggleMenu();" class="block w-full text-left text-sm font-bold tracking-widest text-slate-700 uppercase">Home</button>
            <button onclick="switchView('services'); toggleMenu();" class="block w-full text-left text-sm font-bold tracking-widest text-slate-700 uppercase">Services</button>
            <button onclick="switchView('process'); toggleMenu();" class="block w-full text-left text-sm font-bold tracking-widest text-slate-700 uppercase">Our Process</button>
            <button onclick="switchView('about'); toggleMenu();" class="block w-full text-left text-sm font-bold tracking-widest text-slate-700 uppercase">About Us</button>
            <button onclick="switchView('intake'); toggleMenu();" class="block w-full text-left text-sm font-bold tracking-widest text-blue-600 uppercase">Order Placement</button>
        </div>
    </nav>

    <main>
        <!-- HOME VIEW -->
        <div id="view-home" class="view-content active">
            <section class="relative pt-24 pb-32 px-6 md:px-12 overflow-hidden">
                <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.25] iris-bg"></div>
                <div class="max-w-7xl mx-auto relative z-10 text-center lg:text-left">
                    <div class="inline-flex items-center space-x-2 bg-blue-50/80 backdrop-blur-sm border border-blue-100 px-4 py-1.5 rounded-full mb-10">
                        <i data-lucide="heart" class="text-blue-800 fill-blue-800 w-3 h-3"></i>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-900">Texas Family Owned & Operated</span>
                    </div>
                    <h1 class="text-6xl md:text-8xl font-serif text-[#0b1121] leading-[1.1] tracking-tight mb-8 max-w-4xl">
                        Keep the <br>
                        <span class="text-blue-600 italic font-medium">love</span> going.
                    </h1>
                    <p class="max-w-lg text-lg md:text-xl text-slate-600 leading-relaxed mb-12 font-medium mx-auto lg:mx-0">
                        Providing peace of mind by honoring your loved ones with professional, care-focused gravesite placements.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-5 justify-center lg:justify-start">
                        <button onclick="switchView('intake')" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-5 rounded-xl font-bold flex items-center justify-center transition-all shadow-[0_15px_30px_-5px_rgba(37,99,235,0.3)] group uppercase tracking-widest text-sm">
                            ORDER A VISIT <i data-lucide="arrow-right" class="ml-3 group-hover:translate-x-1 transition-transform w-4 h-4"></i>
                        </button>
                        <button onclick="switchView('services')" class="bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-800 px-10 py-5 rounded-xl font-bold hover:bg-slate-50 transition-all shadow-sm uppercase tracking-widest text-sm">
                            OUR SERVICES
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- INTAKE VIEW -->
        <div id="view-intake" class="view-content bg-stone-50 min-h-screen pt-20 pb-40">
            <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.1] iris-bg"></div>
            <div class="max-w-4xl mx-auto px-6 relative z-10">
                <div id="intake-form-container" class="bg-white rounded-[3rem] shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="flex border-b border-stone-100 overflow-x-auto scrollbar-hide">
                        <div id="step-indicator-1" class="flex-1 min-w-[150px] py-4 text-center text-[10px] font-black tracking-widest border-b-4 border-blue-600 text-blue-600">1. YOUR INFO</div>
                        <div id="step-indicator-2" class="flex-1 min-w-[150px] py-4 text-center text-[10px] font-black tracking-widest border-b-4 border-transparent text-slate-300">2. LOVED ONE</div>
                        <div id="step-indicator-3" class="flex-1 min-w-[150px] py-4 text-center text-[10px] font-black tracking-widest border-b-4 border-transparent text-slate-300">3. SERVICE</div>
                        <div id="step-indicator-4" class="flex-1 min-w-[150px] py-4 text-center text-[10px] font-black tracking-widest border-b-4 border-transparent text-slate-300">4. CONFIRM</div>
                    </div>

                    <form id="intake-form" class="p-8 md:p-16 space-y-12" onsubmit="handleFormSubmit(event)">
                        
                        <!-- Section 1: Your Information -->
                        <div id="step-1" class="space-y-8 animate-in fade-in">
                            <div class="space-y-2">
                                <h3 class="text-4xl font-serif text-[#0b1121]">Section 1: Your Information</h3>
                                <p class="text-slate-500 font-serif italic text-lg">Please complete the below so we can process your order.</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">1. Full Name *</label>
                                    <input type="text" id="cust-name" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" placeholder="John Doe">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">2. Phone Number *</label>
                                    <input type="tel" id="cust-phone" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" placeholder="(555) 000-0000">
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">3. Email Address *</label>
                                <input type="email" id="cust-email" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" placeholder="email@example.com">
                            </div>
                            
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase block">4. Preferred Contact Method</label>
                                <div class="flex flex-wrap gap-8">
                                    <label class="flex items-center gap-3 text-sm font-bold text-slate-700 cursor-pointer">
                                        <input type="radio" name="contact-method" value="Text" checked class="w-5 h-5 text-blue-600"> Text
                                    </label>
                                    <label class="flex items-center gap-3 text-sm font-bold text-slate-700 cursor-pointer">
                                        <input type="radio" name="contact-method" value="Email" class="w-5 h-5 text-blue-600"> Email
                                    </label>
                                    <label class="flex items-center gap-3 text-sm font-bold text-slate-700 cursor-pointer">
                                        <input type="radio" name="contact-method" value="Phone Call" class="w-5 h-5 text-blue-600"> Phone Call
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-2 pt-4 border-t border-slate-100">
                                <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase block leading-relaxed">How would you like the photo after placing flowers be sent to you?</label>
                                <input type="text" id="photo-delivery-pref" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" placeholder="e.g. Text to my cell, email, etc.">
                            </div>

                            <button type="button" onclick="goToStep(2)" class="w-full bg-blue-600 text-white py-5 rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all shadow-xl flex items-center justify-center gap-2">
                                Next: Loved One Info <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Section 2: Loved One Information -->
                        <div id="step-2" class="hidden space-y-8 animate-in fade-in">
                            <h3 class="text-4xl font-serif text-[#0b1121]">Section 2: Loved One Information</h3>
                            
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">5. Name of Loved One(s) (As it appears on headstone) *</label>
                                    <input type="text" id="loved-one-name" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all">
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">6. Cemetery Name *</label>
                                        <input type="text" id="cem-name" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">7. Cemetery Address (if known)</label>
                                        <input type="text" id="cem-address" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">8. Section / Lot / Plot Number (if known)</label>
                                        <input type="text" id="cem-plot-details" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">Birthdate / Date of Deceased</label>
                                        <input type="text" id="dates-info" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" placeholder="MM/DD/YYYY - MM/DD/YYYY">
                                    </div>
                                </div>
                                
                                <div class="space-y-2 pt-4 border-t border-slate-100">
                                    <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase block leading-relaxed">Are there any other family members that may want to place flowers as well? (Please supply contact info)</label>
                                    <textarea id="family-contacts" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" rows="3" placeholder="Name, Phone, Email..."></textarea>
                                </div>

                                <div class="p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-center">
                                    <p class="text-[10px] font-black text-slate-400 tracking-widest uppercase mb-2">9. Photo of Headstone (Optional but Helpful)</p>
                                    <p class="text-xs text-slate-500 italic">Please share or send via preferred contact method once order is placed.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <button type="button" onclick="goToStep(1)" class="flex-1 border border-slate-200 text-slate-600 py-5 rounded-2xl font-bold hover:bg-slate-50 transition-all">Back</button>
                                <button type="button" onclick="goToStep(3)" class="flex-[2] bg-blue-600 text-white py-5 rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all">Continue to Service</button>
                            </div>
                        </div>

                        <!-- Section 3: Service Selection -->
                        <div id="step-3" class="hidden space-y-8 animate-in fade-in">
                            <h3 class="text-4xl font-serif text-[#0b1121]">Section 3: Service Selection</h3>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <input type="radio" name="service-type" id="service-spring" value="Spring Placement" checked class="hidden custom-radio">
                                <label for="service-spring" class="p-6 border-2 border-stone-100 rounded-2xl flex items-center justify-between cursor-pointer hover:border-blue-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 border-2 border-stone-300 rounded-full flex items-center justify-center radio-dot transition-all">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 radio-dot-inner transition-all"></div>
                                        </div>
                                        <span class="font-bold text-slate-800 text-lg">Spring Placement</span>
                                    </div>
                                    <span class="text-blue-600 font-bold">$50.00</span>
                                </label>

                                <input type="radio" name="service-type" id="service-fall" value="Fall Placement" class="hidden custom-radio">
                                <label for="service-fall" class="p-6 border-2 border-stone-100 rounded-2xl flex items-center justify-between cursor-pointer hover:border-blue-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 border-2 border-stone-300 rounded-full flex items-center justify-center radio-dot transition-all">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 radio-dot-inner transition-all"></div>
                                        </div>
                                        <span class="font-bold text-slate-800 text-lg">Fall Placement</span>
                                    </div>
                                    <span class="text-blue-600 font-bold">$50.00</span>
                                </label>

                                <input type="radio" name="service-type" id="service-birthday" value="Birthday Memorial" class="hidden custom-radio">
                                <label for="service-birthday" class="p-6 border-2 border-stone-100 rounded-2xl flex items-center justify-between cursor-pointer hover:border-blue-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 border-2 border-stone-300 rounded-full flex items-center justify-center radio-dot transition-all">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 radio-dot-inner transition-all"></div>
                                        </div>
                                        <span class="font-bold text-slate-800 text-lg">Birthday Memorial</span>
                                    </div>
                                    <span class="text-slate-400 text-xs italic">Price upon request</span>
                                </label>

                                <input type="radio" name="service-type" id="service-anniversary" value="Anniversary of Passing" class="hidden custom-radio">
                                <label for="service-anniversary" class="p-6 border-2 border-stone-100 rounded-2xl flex items-center justify-between cursor-pointer hover:border-blue-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 border-2 border-stone-300 rounded-full flex items-center justify-center radio-dot transition-all">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 radio-dot-inner transition-all"></div>
                                        </div>
                                        <span class="font-bold text-slate-800 text-lg">Anniversary of Passing</span>
                                    </div>
                                    <span class="text-slate-400 text-xs italic">Price upon request</span>
                                </label>

                                <input type="radio" name="service-type" id="service-custom" value="Custom Date" class="hidden custom-radio">
                                <label for="service-custom" class="p-6 border-2 border-stone-100 rounded-2xl flex items-center justify-between cursor-pointer hover:border-blue-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 border-2 border-stone-300 rounded-full flex items-center justify-center radio-dot transition-all">
                                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 radio-dot-inner transition-all"></div>
                                        </div>
                                        <span class="font-bold text-slate-800 text-lg">Custom Date</span>
                                    </div>
                                    <span class="text-slate-400 text-xs italic">Price upon request</span>
                                </label>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-stone-100">
                                <h4 class="text-xl font-serif text-[#0b1121]">Section 4: Special Instructions</h4>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 tracking-widest uppercase">11. Special Notes or Message</label>
                                    <textarea id="order-notes" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all" rows="4"></textarea>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <button type="button" onclick="goToStep(2)" class="flex-1 border border-slate-200 text-slate-600 py-5 rounded-2xl font-bold hover:bg-slate-50 transition-all">Back</button>
                                <button type="button" onclick="goToStep(4)" class="flex-[2] bg-blue-600 text-white py-5 rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all">Final Review</button>
                            </div>
                        </div>

                        <!-- Section 5: Payment & Signature -->
                        <div id="step-4" class="hidden space-y-8 animate-in fade-in">
                            <h3 class="text-4xl font-serif text-[#0b1121]">Section 5: Payment & Acknowledgment</h3>
                            
                            <div class="bg-blue-50 p-8 rounded-3xl border border-blue-100 space-y-8">
                                <label class="flex items-start gap-4 cursor-pointer group">
                                    <input type="checkbox" id="payment-ack" required class="mt-1 w-6 h-6 text-blue-600 rounded-lg border-blue-300 focus:ring-blue-500">
                                    <span class="text-sm md:text-base text-blue-900 leading-relaxed font-bold">
                                        12. I understand payment is required before placement. If for any reason the site is not located, your money will be refunded in full.
                                    </span>
                                </label>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-blue-200/50">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-blue-800 tracking-widest uppercase">Digital Signature</label>
                                        <input type="text" id="signature" required class="w-full p-4 bg-white border border-blue-200 rounded-xl outline-none shadow-sm focus:ring-2 focus:ring-blue-600" placeholder="Type full name">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-blue-800 tracking-widest uppercase">Date</label>
                                        <input type="text" id="sign-date" readonly class="w-full p-4 bg-blue-100/50 border border-blue-200 rounded-xl text-blue-900 font-bold cursor-default">
                                    </div>
                                </div>
                            </div>

                            <div id="form-message"></div>
                            
                            <div class="flex gap-4">
                                <button type="button" onclick="goToStep(3)" class="flex-1 border border-slate-200 text-slate-600 py-5 rounded-2xl font-bold hover:bg-slate-50 transition-all">Back</button>
                                <button type="submit" id="submit-btn" class="flex-[2] bg-emerald-600 text-white py-5 rounded-2xl font-bold text-lg hover:bg-emerald-700 shadow-xl transition-all flex items-center justify-center gap-2">
                                    Submit Order Request <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Success Message -->
                <div id="success-message" class="hidden bg-white rounded-[3rem] p-16 md:p-24 text-center shadow-2xl border border-slate-100 animate-in zoom-in">
                    <div class="w-24 h-24 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-sm">
                        <i data-lucide="check-circle" class="w-12 h-12"></i>
                    </div>
                    <h3 class="text-4xl font-serif text-[#0b1121] mb-4">Request Received</h3>
                    <p class="text-xl text-slate-500 max-w-sm mx-auto font-serif italic mb-10 leading-relaxed">
                        Craig or Michelle will be in touch shortly to finalize your visit details. We have recorded your request with care.
                    </p>
                    <button onclick="location.reload()" class="bg-blue-600 text-white px-10 py-4 rounded-full font-bold uppercase tracking-widest text-[10px] hover:bg-blue-700 shadow-lg transition-all">Start New Request</button>
                </div>
            </div>
        </div>

        <!-- SERVICES VIEW -->
        <div id="view-services" class="view-content">
            <section class="relative pt-20 pb-32 px-6 md:px-12 overflow-hidden min-h-screen bg-white">
                <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.25] iris-bg"></div>
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="text-center mb-20 space-y-6">
                        <h2 class="text-5xl md:text-7xl font-serif text-[#0b1121] tracking-tight">Professional Services</h2>
                        <p class="text-xl font-serif italic text-slate-500 tracking-wide">"Distance should never mean forgotten."</p>
                    </div>
                    <div class="grid lg:grid-cols-2 gap-8 mb-24">
                        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-50 relative flex flex-col overflow-hidden group">
                            <div class="h-64 bg-slate-50 relative overflow-hidden">
                                <div class="absolute top-8 right-8 bg-blue-600 text-white font-bold px-6 py-2 rounded-full shadow-lg z-20">$50.00</div>
                            </div>
                            <div class="p-10 md:p-14 space-y-8">
                                <h3 class="text-4xl font-serif text-[#0b1121]">Seasonal Placements</h3>
                                <div class="space-y-6">
                                    <div class="flex items-start space-x-4"><div class="mt-1 bg-blue-50 p-1 rounded-full text-blue-600"><i data-lucide="check-circle" class="w-4 h-4"></i></div><p class="text-lg text-slate-700 leading-relaxed"><span class="font-bold">Spring Placement:</span> Easter and Mother's Day themes.</p></div>
                                    <div class="flex items-start space-x-4"><div class="mt-1 bg-blue-50 p-1 rounded-full text-blue-600"><i data-lucide="check-circle" class="w-4 h-4"></i></div><p class="text-lg text-slate-700 leading-relaxed"><span class="font-bold">Fall/Winter Placement:</span> Christmas and holiday arrangements.</p></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-50 flex flex-col overflow-hidden">
                            <div class="h-64 bg-white flex items-center justify-center p-12"><img src="images/DFM LOGO TP.png" class="h-32 w-auto opacity-80" alt="Logo Icon"></div>
                            <div class="p-10 md:p-14 flex-grow"><h3 class="text-4xl font-serif text-[#0b1121]">Special Occasions</h3><p class="text-slate-500 italic font-serif text-lg">Birthdays and Anniversaries handled with personal care.</p></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div id="view-process" class="view-content">
            <section class="relative pt-20 pb-40 px-6 md:px-12 overflow-hidden min-h-screen bg-slate-50/30">
                <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.25] iris-bg"></div>
                <div class="max-w-5xl mx-auto relative z-10 text-center">
                    <h2 class="text-5xl md:text-6xl font-serif text-[#0b1121] tracking-tight mb-12">Professional Integrity</h2>
                    <div class="max-w-3xl mx-auto space-y-12">
                        <div class="bg-white p-10 rounded-[2.5rem] shadow-sm flex items-center gap-8 text-left border border-stone-100">
                            <div class="bg-blue-50 p-6 rounded-3xl text-blue-600"><i data-lucide="map-pin" class="w-8 h-8"></i></div>
                            <div><h4 class="text-2xl font-serif mb-2">Coordination</h4><p class="text-slate-500">Every visit is verified and mapped with precision.</p></div>
                        </div>
                        <div class="bg-white p-10 rounded-[2.5rem] shadow-sm flex items-center gap-8 text-left border border-stone-100">
                            <div class="bg-blue-50 p-6 rounded-3xl text-blue-600"><i data-lucide="camera" class="w-8 h-8"></i></div>
                            <div><h4 class="text-2xl font-serif mb-2">Visual Proof</h4><p class="text-slate-500">Photo verification sent directly to your phone.</p></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div id="view-about" class="view-content">
            <section class="relative pt-20 pb-40 px-6 md:px-12 min-h-screen bg-white">
                <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.25] iris-bg"></div>
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <div class="relative group">
                            <div class="relative bg-white p-6 rounded-[3rem] shadow-xl border border-blue-50">
                                <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden"><img src="images/upscaler-Business Card (1).pdf.pdf-2x.jpg" alt="Craig & Michelle Moran" class="w-full h-full object-cover"></div>
                            </div>
                        </div>
                        <div class="space-y-10 lg:pl-10">
                            <h2 class="text-6xl font-serif text-[#0b1121]">A Labor of Love</h2>
                            <p class="font-serif italic text-2xl text-slate-500 leading-relaxed">"Distance should never mean forgotten. We are here to bridge that gap with care."</p>
                            <div class="grid sm:grid-cols-2 gap-12 pt-12 border-t border-slate-100">
                                <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Craig Moran</p><a href="tel:8157212355" class="text-2xl font-bold text-blue-800 tracking-tight">(815) 721-2355</a></div>
                                <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Michelle Moran</p><a href="tel:8178893271" class="text-2xl font-bold text-blue-800 tracking-tight">(817) 889-3271</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="bg-[#0b1121] pt-24 pb-12 px-6 md:px-12 text-white relative">
        <div class="max-w-7xl mx-auto relative z-10 text-center md:text-left">
            <div class="grid lg:grid-cols-12 gap-16 mb-24 text-left">
                <div class="lg:col-span-4 space-y-8">
                    <img src="images/DFM LOGO TP.png" alt="Footer Logo" class="h-[5.4rem] w-auto grayscale brightness-0 invert opacity-80">
                    <p class="text-slate-400 font-serif italic text-lg leading-relaxed">"Keeping love present, even when you can't be there."</p>
                </div>
                <div class="lg:col-span-4 lg:pl-12">
                    <h4 class="text-[10px] font-black tracking-[0.3em] uppercase text-white/80 mb-8">Contact Information</h4>
                    <div class="space-y-6 text-slate-400">
                        <div class="flex items-center space-x-4"><i data-lucide="phone" class="w-4 h-4 text-blue-500"></i><span>(817) 889-5271</span></div>
                        <div class="flex items-center space-x-4"><i data-lucide="mail" class="w-4 h-4 text-blue-500"></i><span>DontForgetMeFlowersTexas@gmail.com</span></div>
                        <div class="flex items-center space-x-4"><i data-lucide="map-pin" class="w-4 h-4 text-blue-500"></i><span>Serving DFW & North Texas</span></div>
                    </div>
                </div>
                <div class="lg:col-span-4 lg:pl-12">
                    <h4 class="text-[10px] font-black tracking-[0.3em] uppercase text-white/80 mb-8">Navigation</h4>
                    <ul class="space-y-6 font-bold text-slate-400 text-xs uppercase tracking-widest">
                        <li onclick="switchView('services')" class="hover:text-blue-400 cursor-pointer transition-colors">Services</li>
                        <li onclick="switchView('about')" class="hover:text-blue-400 cursor-pointer transition-colors">Our Story</li>
                        <li onclick="switchView('intake')" class="text-blue-400 underline underline-offset-8 cursor-pointer transition-colors">Book a Visit</li>
                    </ul>
                </div>
            </div>
            <div class="pt-12 border-t border-white/5 text-center text-[9px] md:text-[10px] font-black tracking-[0.4em] text-white/30 uppercase">
                <div>© 2026 DON'T FORGET ME FLOWERS | PROFESSIONALLY MANAGED GRAVESITE CARE</div>
                <div class="mt-2 tracking-[0.2em] opacity-50 lowercase">
                    site by <a href="https://lylestechconsulting.com/" target="_blank" class="hover:text-blue-400 transition-colors uppercase">LTEC</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('menu-icon');
            menu.classList.toggle('hidden');
            const isHidden = menu.classList.contains('hidden');
            icon.setAttribute('data-lucide', isHidden ? 'menu' : 'x');
            lucide.createIcons();
        }

        function switchView(viewId) {
            document.querySelectorAll('.view-content').forEach(v => v.classList.remove('active'));
            document.getElementById('view-' + viewId).classList.add('active');
            
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'pb-1');
                link.classList.add('text-slate-500');
            });
            const activeBtn = document.getElementById('btn-' + (viewId === 'intake' ? 'services' : viewId));
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-500');
                activeBtn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'pb-1');
            }

            if(viewId === 'intake') {
                document.getElementById('sign-date').value = new Date().toLocaleDateString();
                goToStep(1);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function goToStep(stepNum) {
            document.querySelectorAll('[id^="step-"]').forEach(el => {
                if (el.id.startsWith('step-indicator-')) return;
                el.classList.add('hidden');
            });
            document.getElementById('step-' + stepNum).classList.remove('hidden');
            
            document.querySelectorAll('[id^="step-indicator-"]').forEach(el => {
                el.classList.remove('border-blue-600', 'text-blue-600');
                el.classList.add('border-transparent', 'text-slate-300');
            });
            const activeInd = document.getElementById('step-indicator-' + stepNum);
            activeInd.classList.remove('border-transparent', 'text-slate-300');
            activeInd.classList.add('border-blue-600', 'text-blue-600');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            
            const orderData = {
                customerName: document.getElementById('cust-name').value,
                customerPhone: document.getElementById('cust-phone').value,
                customerEmail: document.getElementById('cust-email').value,
                contactMethod: document.querySelector('input[name="contact-method"]:checked').value,
                photoDeliveryPreference: document.getElementById('photo-delivery-pref').value,
                lovedOneName: document.getElementById('loved-one-name').value,
                cemeteryName: document.getElementById('cem-name').value,
                cemeteryAddress: document.getElementById('cem-address').value,
                plotDetails: document.getElementById('cem-plot-details').value,
                datesInfo: document.getElementById('dates-info').value,
                familyReferralContacts: document.getElementById('family-contacts').value,
                serviceType: document.querySelector('input[name="service-type"]:checked').value,
                notes: document.getElementById('order-notes').value,
                signature: document.getElementById('signature').value,
                signedDate: document.getElementById('sign-date').value
            };

            if (window.submitOrder) {
                await window.submitOrder(orderData);
            }
        }
    </script>
</body>
</html>