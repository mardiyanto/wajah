<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition Absensi</title>
    <script src="face/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #video {
            border: 1px solid #ccc;
            width: 640px;
            height: 480px;
            display: block;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <h1 class="text-center">Absensi Berbasis Wajah</h1>
    <video id="video" autoplay playsinline></video>

    <script>
        const video = document.getElementById('video');
        let faceMatcher;

        // Fungsi memuat model
        const loadModels = async () => {
            Swal.fire({
                title: 'Memuat model...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('face/weights'),
                faceapi.nets.ssdMobilenetv1.loadFromUri('face/weights'),
                faceapi.nets.faceLandmark68Net.loadFromUri('face/weights'),
                faceapi.nets.faceRecognitionNet.loadFromUri('face/weights'),
                faceapi.nets.ageGenderNet.loadFromUri('face/weights'),
                faceapi.nets.faceExpressionNet.loadFromUri('face/weights'),
            ]);

            Swal.close();
        };

        // Fungsi memuat wajah dari database
        const loadFacesFromDatabase = async () => {
            try {
                const response = await fetch('get_faces.php');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();
                const labeledDescriptors = [];

                for (const face of data) {
                    const img = document.createElement('img');
                    img.src = face.image_path;
                    img.alt = face.username;

                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
                    if (!detection) {
                        console.warn(`Wajah tidak ditemukan untuk ${face.username}`);
                        continue;
                    }

                    labeledDescriptors.push({
                        label: `${face.id}|${face.name}|${face.username}`,
                        descriptor: detection.descriptor
                    });
                }

                faceMatcher = new faceapi.FaceMatcher(
                    labeledDescriptors.map(item => new faceapi.LabeledFaceDescriptors(item.label, [item.descriptor])),
                    0.4
                );
            } catch (error) {
                Swal.fire('Error', 'Gagal memuat data wajah dari database.', 'error');
                console.error('Error loading faces:', error);
            }
        };

        // Deteksi wajah dari webcam
        const detectFromWebcam = async () => {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection && faceMatcher) {
                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                if (bestMatch.distance > 0.4) {
                    Swal.fire('Tidak Cocok!', 'Wajah tidak sesuai dengan database.', 'error');
                } else {
                    const [id, name, username] = bestMatch.label.split('|');
                    Swal.fire('Cocok!', `ID: ${id}\nName: ${name}\nUsername: ${username}`, 'success');

                    const formData = new FormData();
                    formData.append('id_wajah', id);
                    formData.append('username', username);

                    try {
                        const response = await fetch('save_absensi.php', {
                            method: 'POST',
                            body: formData,
                        });

                        const result = await response.json();
                        if (result.success) {
                            Swal.fire('Berhasil', 'Data absensi berhasil disimpan.', 'success');
                        } else if (result.message === 'Anda sudah absen hari ini.') {
                            Swal.fire('Sudah Absen', 'Anda sudah absen hari ini.', 'info');
                        } else {
                            Swal.fire('Error', 'Gagal menyimpan data absensi.', 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data absensi.', 'error');
                    }
                }
            } else {
                console.log('Tidak ada wajah terdeteksi.');
            }
            setTimeout(detectFromWebcam, 1000);
        };

        // Mulai video stream
        const startVideoStream = async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 640, height: 480 },
                    audio: false
                });
                video.srcObject = stream;
                video.addEventListener('play', detectFromWebcam);
            } catch (error) {
                Swal.fire('Error', 'Tidak dapat mengakses webcam.', 'error');
            }
        };

        // Inisialisasi aplikasi
        const initializeApp = async () => {
            await loadModels();
            await loadFacesFromDatabase();
            await startVideoStream();
        };

        initializeApp();
    </script>
</body>
</html>
