{{-- resources/views/ktvtc/finance/high-school/cards/partials/id-card.blade.php --}}

@php
    // Define asset paths for better maintainability
    $assets = [
        'logo' => asset('images/logo/main-logo.png'),
        'header_bg' => asset('images/id-card/header-bg.png'),
        'footer_bg' => asset('images/id-card/footer-bg.png'),
        'default_avatar' => asset('images/default/avatar.png'),
    ];
    
    $student = $card->student;
    $photoUrl = $student && $student->profile_picture 
        ? asset('storage/' . $student->profile_picture) 
        : $assets['default_avatar'];
    
    $qrUrl = $card->qr_code && Storage::disk('public')->exists($card->qr_code)
        ? asset('storage/' . $card->qr_code)
        : null;
@endphp

<div class="bg-white rounded-2xl shadow-2xl overflow-hidden font-sans text-[#1a1a1a] max-w-4xl mx-auto" id="id-card">
    <!-- ============ FRONT OF THE CARD ============ -->
    <div>
        <!-- HEADER -->
        <div class="relative flex items-center justify-between h-28 md:h-32 px-6 z-10">
            <!-- Left Section: Logo & School Details -->
            <div class="flex items-center gap-4 flex-shrink-0">
                <img 
                    src="{{ $assets['logo'] }}" 
                    alt="Kenswed Logo" 
                    class="w-24 h-24 md:w-28 md:h-28 rounded-full object-cover border-2 border-gray-100 bg-white shrink-0" 
                />
                <div class="flex flex-col justify-center">
                    <h1 class="text-xl md:text-2xl font-black uppercase leading-tight">
                        Kenswed<br>Secondary School
                    </h1>
                    <div class="bg-[#d81524] text-white self-start px-2 md:px-3 py-0.5 text-[8px] md:text-xs font-bold uppercase flex items-center gap-1.5 mt-1 rounded-sm shadow-sm whitespace-nowrap">
                        <span class="block w-2.5 md:w-4 h-[1.5px] bg-white"></span>
                        it seems impossible until it's done
                        <span class="block w-2.5 md:w-4 h-[1.5px] bg-white"></span>
                    </div>
                </div>
            </div>

            <!-- Right Section: Red Banner -->
            <div class="absolute right-0 top-0 h-full w-[55%] md:w-[48%] bg-[url('{{ $assets['header_bg'] }}')] bg-cover bg-center flex items-center justify-center pl-6">
                <h2 class="text-white text-sm md:text-xl lg:text-2xl font-bold uppercase tracking-wider text-center">
                    Students' Identity Card
                </h2>
            </div>
        </div>

        <!-- MIDDLE SECTION -->
        <div class="grid grid-cols-12 gap-4 md:gap-8 p-6 md:p-10 items-center">
            <!-- Photo -->
            <div class="col-span-4 md:col-span-3">
                <img 
                    src="{{ $photoUrl }}" 
                    alt="{{ $student->full_name ?? $card->student_name }}" 
                    class="w-full aspect-square object-cover rounded-[35px] border-[5px] border-[#d81524] shadow-sm" 
                />
            </div>

            <!-- Student Info -->
            <div class="col-span-5 md:col-span-6 flex flex-col justify-center gap-1 md:gap-2">
                <h3 class="text-xl md:text-2xl font-bold uppercase tracking-wide mb-1 md:mb-2">
                    {{ $student->full_name ?? $card->student_name }}
                </h3>
                
                <div class="grid grid-cols-[auto_auto_1fr] gap-x-2 md:gap-x-3 gap-y-0.5 md:gap-y-1 text-xs md:text-[15px] font-medium">
                    <span>Adm No</span>
                    <span>:</span>
                    <span class="text-[#d81524]">{{ $student->admission_number ?? $card->student_admission_number }}</span>
                    
                    <span>Class</span>
                    <span>:</span>
                    <span class="text-[#d81524]">{{ $student->class ?? $card->student_class }}</span>
                    
                    <span>Stream</span>
                    <span>:</span>
                    <span class="text-[#d81524]">{{ $student->stream ?? 'N/A' }}</span>
                    
                    <span>Date of Birth</span>
                    <span>:</span>
                    <span class="text-[#d81524]">{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</span>
                    
                    <span>Gender</span>
                    <span>:</span>
                    <span class="text-[#d81524]">{{ ucfirst($student->gender ?? 'N/A') }}</span>
                    
                    <span>Card No</span>
                    <span>:</span>
                    <span class="text-[#d81524] font-mono text-xs">{{ $card->card_number }}</span>
                </div>
            </div>

            <!-- QR Code -->
            <div class="col-span-3 flex justify-center md:justify-end">
                <div class="w-56 md:w-72 aspect-square border-2 border-dashed border-gray-400 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden p-2">
                    @if($qrUrl)
                        <img 
                            src="{{ $qrUrl }}" 
                            alt="Student QR Code" 
                            class="w-full h-full object-contain"
                            loading="lazy"
                        >
                    @else
                        <div class="text-center text-gray-400 text-xs">
                            <i class="fas fa-qrcode text-3xl block mb-1"></i>
                            No QR Code
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="flex flex-row w-full h-32 md:h-36">
            <!-- Bottom Left: Red Background -->
            <div class="flex-1 bg-[url('{{ $assets['footer_bg'] }}')] bg-cover bg-center rounded-tr-[60px] md:rounded-tr-[80px] p-4 md:p-6 flex flex-col justify-center gap-1.5 md:gap-2.5">
                <div class="flex flex-wrap items-center gap-x-3 md:gap-x-6 gap-y-1">
                    <div class="flex items-center gap-2 text-white text-[10px] md:text-sm font-medium">
                        <div class="w-7 h-7 bg-black rounded-full flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-phone text-xs text-[#d81524]"></i>
                        </div>
                        <span class="whitespace-nowrap">+254 743 237 432</span>
                    </div>
                    <div class="flex items-center gap-2 text-white text-[10px] md:text-sm font-medium">
                        <div class="w-7 h-7 bg-black rounded-full flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-globe text-xs text-[#d81524]"></i>
                        </div>
                        <span class="whitespace-nowrap">www.kenswed.org</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-white text-[10px] md:text-sm font-medium">
                    <div class="w-7 h-7 bg-black rounded-full flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-location-dot text-xs text-[#d81524]"></i>
                    </div>
                    <span class="truncate">Kibiko, Off Maragara road, Kajiado West</span>
                </div>
            </div>

            <!-- Bottom Right: Signature -->
            <div class="w-1/4 bg-white flex flex-col items-center justify-end pb-4 md:pb-6 px-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" width="120" height="40" class="opacity-90">
                    <path d="M10,40 C40,80 80,-10 110,50 C140,110 170,20 190,40" fill="none" stroke="#1a1a1a" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="w-3/4 h-0.5 bg-black mt-1 mb-0.5"></div>
                <p class="font-bold uppercase text-[10px] md:text-xs tracking-wider mt-1 text-[#1a1a1a]">Principal</p>
            </div>
        </div>
    </div>

    <!-- ============ BACK OF THE CARD ============ -->
    <div class="border-t border-gray-200 mt-1">
        <div class="p-8 md:p-12 flex-grow">
            <h2 class="text-[#d81524] text-2xl md:text-3xl font-bold uppercase mb-6">School Policies</h2>
            
            <ul class="space-y-3 text-[14px] md:text-[16px] text-gray-800 leading-relaxed">
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 bg-[#d81524] mt-2 flex-shrink-0 rounded-[2px]"></span>
                    <span>Present this card upon request by any school official or security personnel.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 bg-[#d81524] mt-2 flex-shrink-0 rounded-[2px]"></span>
                    <span>This ID is non-transferable and remains the property of Kenswed Sec School.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 bg-[#d81524] mt-2 flex-shrink-0 rounded-[2px]"></span>
                    <span>Misuse or alteration of this card may lead to disciplinary action.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 bg-[#d81524] mt-2 flex-shrink-0 rounded-[2px]"></span>
                    <span>Loss must be reported immediately to the school Administration office.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 bg-[#d81524] mt-2 flex-shrink-0 rounded-[2px]"></span>
                    <span>Replacement cards are available at a fee.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 bg-[#d81524] mt-2 flex-shrink-0 rounded-[2px]"></span>
                    <span>All students are expected to adhere to the code of conduct available on the school website.</span>
                </li>
            </ul>
        </div>

        <!-- Footer (Back) -->
        <div class="flex flex-row w-full h-32 md:h-36 border-t border-gray-200">
            <div class="flex-1 bg-[url('{{ $assets['footer_bg'] }}')] bg-cover bg-center rounded-tr-[60px] md:rounded-tr-[80px] p-4 md:p-6 flex flex-col justify-center gap-1.5 md:gap-2.5">
                <div class="flex flex-wrap items-center gap-x-3 md:gap-x-6 gap-y-1">
                    <div class="flex items-center gap-2 text-white text-[10px] md:text-sm font-medium">
                        <div class="w-7 h-7 bg-black rounded-full flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-phone text-xs text-[#d81524]"></i>
                        </div>
                        <span class="whitespace-nowrap">+254 743 237 432</span>
                    </div>
                    <div class="flex items-center gap-2 text-white text-[10px] md:text-sm font-medium">
                        <div class="w-7 h-7 bg-black rounded-full flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-globe text-xs text-[#d81524]"></i>
                        </div>
                        <span class="whitespace-nowrap">www.kenswed.org</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-white text-[10px] md:text-sm font-medium">
                    <div class="w-7 h-7 bg-black rounded-full flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-location-dot text-xs text-[#d81524]"></i>
                    </div>
                    <span class="truncate">Kibiko, Off Maragara road, Kajiado West</span>
                </div>
            </div>
            <div class="w-1/4 bg-white flex flex-col items-center justify-end pb-4 md:pb-6 px-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" width="120" height="40" class="opacity-90">
                    <path d="M10,40 C40,80 80,-10 110,50 C140,110 170,20 190,40" fill="none" stroke="#1a1a1a" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="w-3/4 h-0.5 bg-black mt-1 mb-0.5"></div>
                <p class="font-bold uppercase text-[10px] md:text-xs tracking-wider mt-1 text-[#1a1a1a]">Principal</p>
            </div>
        </div>
    </div>
</div>