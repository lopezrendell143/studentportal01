const video = document.getElementById('video');
const statusText = document.getElementById('status-text');
const statusBox = document.getElementById('status-box');
const errorBox = document.getElementById('error-box');
const registerBtn = document.getElementById('registerBtn');

// Simple online model files directory
const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models/';

// Get the active logged in student profile tracking signature
const activeStudentNode = localStorage.getItem('user_node');

async function init() {
    if (!activeStudentNode) {
        showError("Please log in using your password first.");
        return;
    }
    
    statusText.innerText = "Connecting to Face ID framework...";
    try {
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        startVideo();
    } catch (e) {
        console.error(e);
        showError("Failed to load online Face ID setup tools. Check internet connection.");
    }
}

function startVideo() {
    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            video.srcObject = stream;
            statusText.innerText = "Camera active. Ready to register.";
            registerBtn.disabled = false;
        })
        .catch(() => showError("Camera access denied or unavailable."));
}

registerBtn.addEventListener('click', async () => {
    statusText.innerText = "Analyzing features... please stay still.";
    errorBox.classList.add('hidden');
    
    const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

    if (detection) {
        const faceDescriptorString = Array.from(detection.descriptor).join(',');
        const targetUrl = `https://studentportal01-9ddef-default-rtdb.asia-southeast1.firebasedatabase.app/Users/${activeStudentNode}.json`;
        
        try {
            statusText.innerText = "Saving face registration data...";
            
            await fetch(targetUrl, {
                method: 'PATCH',
                body: JSON.stringify({
                    face_descriptor: faceDescriptorString
                }),
                headers: { 'Content-Type': 'application/json' }
            });
            
            statusText.innerText = "Face ID Linked Successfully!";
            statusBox.className = "bg-green-50 text-green-600 p-3 rounded-lg text-sm mb-4 border border-green-100 font-bold text-center";
        } catch (err) {
            showError("Failed to save data to Firebase server node.");
        }
    } else {
        showError("Face not detected clearly. Try shifting your posture.");
    }
});

function showError(msg) {
    errorBox.textContent = msg;
    errorBox.classList.remove('hidden');
    statusBox.classList.add('hidden');
}

init();