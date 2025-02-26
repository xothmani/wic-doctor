@if($activeDoctor)
    @php
        $decodedName = json_decode($activeDoctor->name, true);
        $doctorName = (json_last_error() === JSON_ERROR_NONE && is_array($decodedName))
            ? ($decodedName['fr'] ?? reset($decodedName))
            : $activeDoctor->name;
    @endphp
    <!-- Outer container without background; applies the gradient border hover effect -->
    <div id="global-doctor-info" class="hover-gradient position-fixed top-0 right-0 m-3 p-0 class-1"
        style="z-index: 9999; top: 10px; right: 10px;">

        <style>
            /* Outer container: no background so that the pseudo-element is visible */
            .class-1 {
                position: fixed !important;
                top: calc(10px - 0.7cm) !important;
                /* Moves 1cm up */
                right: calc(10px + 6cm) !important;
                /* Moves 1cm to the left */
                z-index: 9999;
                border: 2px solid transparent !important;
                animation: floatEffect 3s ease-in-out infinite alternate;
                /* Floating animation */
            }

            /* Gradient border */
            .class-1::before {
                content: "";
                position: absolute !important;
                top: -2px;
                left: -2px;
                right: -2px;
                bottom: -2px;
                background: linear-gradient(45deg, #ff7e5f, #feb47b) !important;
                border-radius: inherit !important;
                z-index: -1 !important;
                opacity: 0;
                transition: opacity 0.3s ease !important;
            }

            /* Hover effect */
            .class-1:hover::before {
                opacity: 1;
                background: linear-gradient(45deg, #43cea2, #185a9d) !important;
            }

            /* Floating effect */
            @keyframes floatEffect {
                0% {
                    transform: translateY(0px);
                }

                100% {
                    transform: translateY(10px);
                }
            }
        </style>
        <!-- Inner container with a background and padding for content -->
        <div class="bg-light p-2 rounded shadow">
            <strong class="small">Docteur actif :</strong>
            <span class="small">{{ $doctorName }}</span>
        </div>
    </div>
@endif