let bluetoothDevice = null; // Menyimpan perangkat Bluetooth
let printerCharacteristic = null; // Menyimpan karakteristik printer
let connectedServiceUUID = null; // Menyimpan UUID service yang terkoneksi

// Daftar UUID service yang umum digunakan oleh thermal printer Bluetooth
const PRINTER_SERVICE_UUIDS = [
  '49535343-fe7d-4ae5-8fa9-9fafd205e455', // Issc Transparent Service (umum untuk thermal printer)
  '000018f0-0000-1000-8000-00805f9b34fb', // Serial Port Profile
  '0000ff00-0000-1000-8000-00805f9b34fb', // Custom Print Service
  '0000ffe0-0000-1000-8000-00805f9b34fb', // HM-10/HM-16 BLE
  'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Nordic UART Service
  '0000fff0-0000-1000-8000-00805f9b34fb', // Generic Printer Service
  '00001101-0000-1000-8000-00805f9b34fb', // SPP (Serial Port Profile)
];

// Daftar UUID characteristic untuk menulis ke printer
const PRINTER_CHARACTERISTIC_UUIDS = [
  '49535343-8841-43f4-a8d4-ecbe34729bb3', // ISSC Transparent TX
  '0000ff02-0000-1000-8000-00805f9b34fb', // Custom Print Char
  '0000ffe1-0000-1000-8000-00805f9b34fb', // HM-10 TX
  '6e400002-b5a3-f393-e0a9-e50e24dcca9e', // Nordic UART RX
  '0000fff2-0000-1000-8000-00805f9b34fb', // Generic Write Char
  '00002af1-0000-1000-8000-00805f9b34fb', // Write Char
];

// Fungsi untuk memeriksa dukungan Web Bluetooth
function checkBluetoothSupport() {
  if (!navigator.bluetooth) {
    throw new Error('Web Bluetooth API tidak didukung di browser ini. Gunakan Chrome/Edge versi terbaru dengan HTTPS atau localhost.');
  }
  return true;
}

// Fungsi untuk mencari characteristic yang bisa ditulis
async function findWritableCharacteristic(service) {
  // Coba UUID yang sudah diketahui
  for (const charUUID of PRINTER_CHARACTERISTIC_UUIDS) {
    try {
      const characteristic = await service.getCharacteristic(charUUID);
      console.log("Karakteristik ditemukan:", charUUID);
      return characteristic;
    } catch (e) {
      // Lanjut ke UUID berikutnya
    }
  }
  
  // Jika tidak ada yang cocok, cari semua characteristic dan pilih yang writable
  try {
    const characteristics = await service.getCharacteristics();
    for (const char of characteristics) {
      const props = char.properties;
      if (props.write || props.writeWithoutResponse) {
        console.log("Karakteristik writable ditemukan:", char.uuid);
        return char;
      }
    }
  } catch (e) {
    console.warn("Gagal mendapatkan daftar karakteristik:", e);
  }
  
  throw new Error('Tidak ditemukan karakteristik yang bisa ditulis');
}

// Fungsi untuk koneksi ke service
async function findPrinterService(server) {
  // Coba UUID yang sudah diketahui
  for (const serviceUUID of PRINTER_SERVICE_UUIDS) {
    try {
      const service = await server.getPrimaryService(serviceUUID);
      console.log("Service ditemukan:", serviceUUID);
      connectedServiceUUID = serviceUUID;
      return service;
    } catch (e) {
      // Lanjut ke UUID berikutnya
    }
  }
  
  // Jika tidak ada yang cocok, coba dapatkan semua services
  try {
    const services = await server.getPrimaryServices();
    if (services.length > 0) {
      console.log("Menggunakan service pertama yang tersedia:", services[0].uuid);
      connectedServiceUUID = services[0].uuid;
      return services[0];
    }
  } catch (e) {
    console.warn("Gagal mendapatkan daftar services:", e);
  }
  
  throw new Error('Tidak ditemukan service printer yang kompatibel');
}

// Fungsi untuk reconnect jika koneksi terputus
async function ensureConnection() {
  if (bluetoothDevice && !bluetoothDevice.gatt.connected) {
    console.log("Koneksi terputus, mencoba reconnect...");
    printerCharacteristic = null;
    const server = await bluetoothDevice.gatt.connect();
    const service = await findPrinterService(server);
    printerCharacteristic = await findWritableCharacteristic(service);
    console.log("Reconnect berhasil!");
  }
}

async function connectToBluetoothPrinter(content) {
  try {
    // Cek dukungan Bluetooth
    checkBluetoothSupport();
    
    // Jika belum ada device atau characteristic, mulai koneksi baru
    if (!bluetoothDevice || !printerCharacteristic) {
      console.log("Belum terhubung ke printer, memulai koneksi...");

      // Memindai perangkat Bluetooth - gunakan acceptAllDevices untuk kompatibilitas maksimal
      bluetoothDevice = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: PRINTER_SERVICE_UUIDS
      });

      if (!bluetoothDevice) {
        throw new Error('Tidak ada perangkat yang dipilih');
      }

      console.log("Perangkat dipilih:", bluetoothDevice.name);

      // Listen untuk disconnect event
      bluetoothDevice.addEventListener('gattserverdisconnected', () => {
        console.log('Koneksi Bluetooth terputus');
        printerCharacteristic = null;
      });

      // Menghubungkan ke perangkat
      console.log("Menghubungkan ke GATT server...");
      const server = await bluetoothDevice.gatt.connect();
      
      console.log("Mencari service printer...");
      const service = await findPrinterService(server);
      
      console.log("Mencari characteristic untuk write...");
      printerCharacteristic = await findWritableCharacteristic(service);
      
      console.log("Koneksi berhasil!");
    } else {
      // Pastikan masih terkoneksi
      await ensureConnection();
    }

    console.log("Mengirim data ke printer...");
    const encoder = new TextEncoder();
    const data = encoder.encode(content + "\n\n");
    
    // Kirim data dalam chunks jika terlalu besar (max 512 bytes per write)
    const chunkSize = 512;
    for (let i = 0; i < data.length; i += chunkSize) {
      const chunk = data.slice(i, i + chunkSize);
      try {
        await printerCharacteristic.writeValue(chunk);
      } catch (writeError) {
        // Coba write tanpa response jika write biasa gagal
        try {
          await printerCharacteristic.writeValueWithoutResponse(chunk);
        } catch (e) {
          throw writeError;
        }
      }
    }

    alert("Berhasil mencetak melalui Bluetooth!");
  } catch (error) {
    console.error("Bluetooth Error:", error);
    
    // Reset koneksi jika error
    if (error.message && (error.message.includes('GATT') || error.message.includes('connect'))) {
      printerCharacteristic = null;
      bluetoothDevice = null;
    }
    
    // Tampilkan pesan error yang lebih informatif
    let errorMessage = "Gagal mencetak melalui Bluetooth.\n\n";
    if (error.message) {
      if (error.message.includes('User cancelled')) {
        errorMessage = "Pemilihan printer dibatalkan.";
      } else if (error.message.includes('not supported')) {
        errorMessage = "Browser tidak mendukung Bluetooth.\nGunakan Chrome atau Edge terbaru dengan HTTPS.";
      } else if (error.message.includes('Bluetooth adapter')) {
        errorMessage = "Bluetooth tidak aktif.\nPastikan Bluetooth di komputer sudah dinyalakan.";
      } else {
        errorMessage += error.message;
      }
    }
    alert(errorMessage);
  }
}

// Fungsi untuk disconnect printer
function disconnectPrinter() {
  if (bluetoothDevice && bluetoothDevice.gatt.connected) {
    bluetoothDevice.gatt.disconnect();
    console.log("Printer disconnected");
  }
  bluetoothDevice = null;
  printerCharacteristic = null;
  connectedServiceUUID = null;
}

// Fungsi untuk cek status koneksi
function isPrinterConnected() {
  return bluetoothDevice && bluetoothDevice.gatt.connected && printerCharacteristic;
}
