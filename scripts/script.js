let connectedDevice = null;

document.getElementById('checkDevices').addEventListener('click', async () => {
    try {
        const device = await navigator.usb.requestDevice({ filters: [] });

        if (!device) {
            throw new Error('디바이스가 선택되지 않았습니다.');
        }

        if (device.opened) {
            await device.close();
        }

        await device.open();

        // 모든 가능한 구성을 출력
        console.log('Configurations:', device.configurations);

        await device.selectConfiguration(1);
        const configuration = device.configuration;

        if (!configuration) {
            throw new Error('구성을 선택할 수 없습니다.');
        }

        // 모든 가능한 인터페이스를 출력
        console.log('Interfaces:', configuration.interfaces);

        // 첫 번째 인터페이스를 사용해 보도록 시도
        const interfaceNumber = configuration.interfaces[0]?.interfaceNumber;

        if (interfaceNumber === undefined) {
            throw new Error('올바른 인터페이스를 찾을 수 없습니다.');
        }

        await device.claimInterface(interfaceNumber);

        connectedDevice = device;

        const deviceInfo = `디바이스 연결됨:\n제품명: ${device.productName}\n제조사: ${device.manufacturerName}\n시리얼 번호: ${device.serialNumber}`;
        document.getElementById('devicesOutput').innerText = deviceInfo;

        const response = await fetch('backend/index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                vendorId: device.vendorId,
                productId: device.productId
            })
        });

        const result = await response.json();
        document.getElementById('devicesOutput').innerText += `\n서버 응답: ${result.message}`;

        const selectedDevice = device.serialNumber;
        updateDeviceInfo(selectedDevice);

        setInterval(() => {
            updateDeviceInfo(selectedDevice);
        }, 5000);
        
    } catch (error) {
        console.error('기기 연결 실패:', error);
        document.getElementById('devicesOutput').innerText = `기기 연결 실패: ${error.message}`;
    }
});

window.onbeforeunload = async () => {
    if (connectedDevice && connectedDevice.opened) {
        try {
            await connectedDevice.close();
            console.log('디바이스 연결 종료됨.');
        } catch (error) {
            console.error('디바이스 연결 종료 실패:', error);
        }
    }
};

async function updateDeviceInfo(serialNumber) {
    try {
        const response = await fetch('backend/updateDeviceInfo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ serialNumber })
        });

        const result = await response.json();
        document.getElementById('devicesOutput').innerText += `\n업데이트된 정보: ${result.message}`;
    } catch (error) {
        console.error('기기 정보 업데이트 실패:', error);
        document.getElementById('devicesOutput').innerText += `\n기기 정보 업데이트 실패: ${error.message}`;
    }
}



function updateDeviceInfo(device) {
    updateLogs(device);
    updateBatteryStatus(device);
}

function updateLogs(device) {
    fetch(`backend/log_analysis.php?device=${device}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('logsOutput').innerHTML = data;
        })
        .catch(error => {
            console.error('Error fetching log analysis:', error);
            document.getElementById('logsOutput').textContent = '로그 분석 데이터를 불러오는 중 오류가 발생했습니다.';
        });
}

function updateBatteryStatus(device) {
    fetch(`backend/battery_status.php?device=${device}`)
        .then(response => response.json())
        .then(jsonData => {
            if (jsonData.error) {
                document.getElementById('batteryOutput').innerHTML = '오류: ' + jsonData.error;
            } else {
                document.getElementById('batteryOutput').innerHTML = '<pre>' + jsonData.status + '</pre>';
            }
        })
        .catch(error => {
            console.error('Error fetching battery status:', error);
            document.getElementById('batteryOutput').textContent = '배터리 상태 데이터를 불러오는 중 오류가 발생했습니다.';
        });
}
