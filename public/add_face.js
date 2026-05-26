const video = document.getElementById('video');
const statusText = document.getElementById('status-text');
const statusBox = document.getElementById('status-box');
const errorBox = document.getElementById('error-box');
const registerBtn = document.getElementById('registerBtn');

const MODEL_URL = 'https://raw.githubusercontent.com/Neet-0/face-api-models/main/';

// AUTOMATIC TRACKING: Grab the unique user identity key from the login token session
const activeStudentNode = localStorage.getItem('user_node');

async function init() {
    if (!activeStudentNode) {
        showError("Authentication failure: Please re-login via password menu first.");
        if (registerBtn) registerBtn.disabled = true;
        return;
    }
    
    statusText.innerText = "Loading AI Core Matrix modules...";
    try {
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        startVideo();
    } catch (e) {
        showError("Failed to synchronize framework files from secure CDN.");
    }
}

function startVideo() {
    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            video.srcObject = stream;
            statusText.innerText = "Biometric capturing window open.";
            if (registerBtn) registerBtn.disabled = false;
        })
        .catch(() => showError("Camera hardware unavailable or denied. Check browser options."));
}

if (registerBtn) {
    registerBtn.addEventListener('click', async () => {
        statusText.innerText = "Scanning facial landmarks... Keep your eyes on the frame!";
        
        const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

        if (detection) {
            const faceDescriptorString = Array.from(detection.descriptor).join(',');
            // Target URL is automatically built around the logged-in student's node
            const targetUrl = `https://studentportal-fc3a9-default-rtdb.asia-southeast1.firebasedatabase.app/Users/${activeStudentNode}.json`;
            
            try {
                statusText.innerText = "Saving biometric signature to your account...";
                await fetch(targetUrl, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        face_descriptor: faceDescriptorString,
                        biometric_setup_complete: true
                    }),
                    headers: { 'Content-Type': 'application/json' }
                });
                
                statusText.innerText = "Face ID configured successfully! You can now log in using your face.";
                statusBox.className = "bg-green-50 text-green-600 p-3 rounded-lg text-sm mb-4 border border-green-100 font-bold";
            } catch (err) {
                showError("Failed to update database cloud records.");
            }
        } else {
            showError("No facial elements recognized. Reposition yourself and try again.");
        }
    });
}

function showError(msg) {
    if (errorBox) { errorBox.textContent = msg; errorBox.classList.remove('hidden'); }
}

init();