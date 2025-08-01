@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Consultation pour {{ $patient->first_name }} {{ $patient->last_name }}</h2>
    <div id="viewer" style="height: 800px; border: 1px solid #ccc;"></div>
</div>

<script src="https://pdfjs.express/lib/webviewer.min.js"></script>
<script>
    WebViewer({
        path: 'https://pdfjs.express/lib', // hébergé chez eux
        initialDoc: "{{ $pdfUrl }}",
        licenseKey: 'Insert_your_license_here'
    }, document.getElementById('viewer')).then(instance => {
        const { documentViewer, annotationManager, PDFNet, docViewer, UI } = instance;

        instance.UI.setHeaderItems(header => {
            header.push({
                type: 'actionButton',
                img: 'https://img.icons8.com/ios-filled/50/save--v1.png',
                title: 'Enregistrer',
                onClick: async () => {
                    const data = await instance.UI.saveDocument({ flatten: true });
                    const blob = new Blob([data], { type: 'application/pdf' });

                    const reader = new FileReader();
                    reader.onloadend = function () {
                        const base64data = reader.result;

                        fetch("{{ route('consultation_perso.save_filled_pdf') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                pdf_data: base64data,
                                patient_id: "{{ $patient->id }}",
                                original_name: "{{ $originalName }}"
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('PDF enregistré avec succès');
                            } else {
                                alert('Erreur lors de l\'enregistrement');
                            }
                        });
                    };

                    reader.readAsDataURL(blob);
                }
            });
        });
    });
</script>
@endsection
