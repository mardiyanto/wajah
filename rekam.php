<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="face/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #video {
            border: 1px solid #ccc;
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Absensi Berbasis Wajah</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Form Input -->
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" class="form-control" placeholder="Masukkan Nama">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" class="form-control" placeholder="Masukkan Username">
                    </div>
                </form>
                <!-- Video Stream -->
                <video id="video" autoplay playsinline width="800" height="600" class="d-block mx-auto"></video>
                <!-- Button -->
                <div class="d-grid mt-3">
                    <button id="capture" class="btn btn-primary">Capture Image</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById("video");

        // Start webcam stream
        navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: 800, height: 600 },
            audio: false
        }).then((stream) => video.srcObject = stream);

        Promise.all([
                faceapi.nets.ageGenderNet.loadFromUri('face/weights'),
                faceapi.nets.ssdMobilenetv1.loadFromUri('face/weights'),
                faceapi.nets.tinyFaceDetector.loadFromUri('face/weights'),
                faceapi.nets.faceLandmark68Net.loadFromUri('face/weights'),
                faceapi.nets.faceRecognitionNet.loadFromUri('face/weights'),
                faceapi.nets.faceExpressionNet.loadFromUri('face/weights')
        ]);

        document.getElementById("capture").addEventListener("click", async () => {
            const canvas = document.createElement('canvas');
            canvas.width = 800;
            canvas.height = 600;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, 800, 600);

            const imageDataURL = canvas.toDataURL('image/png');
            const detections = await faceapi.detectSingleFace(canvas).withFaceLandmarks().withFaceDescriptor();

            if (detections) {
                const descriptor = detections.descriptor;
                const name = document.getElementById("name").value;
                const username = document.getElementById("username").value;

                fetch('proses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username,
                        name,
                        image: imageDataURL,
                        descriptor
                    })
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          Swal.fire('Berhasil Daftar Wajah!', '', 'success').then(() => {
                              window.location.href = 'index.php';
                          });
                      } else {
                          Swal.fire('Gagal Daftar Wajah', data.error, 'error');
                      }
                  });
            } else {
                Swal.fire('Wajah Tidak Terdeteksi!', '', 'error');
            }
        });
    </script>
</body>
</html>
