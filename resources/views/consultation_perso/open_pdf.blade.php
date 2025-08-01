<script src="https://pdftron.s3.amazonaws.com/downloads/pl/webviewer/lib/webviewer.min.js"></script>
<script>
    WebViewer({
        path: 'https://pdftron.s3.amazonaws.com/downloads/pl/webviewer/lib',
        initialDoc: "{{ $pdfUrl }}",
        licenseKey: '', // Facultatif si version d'essai
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
