let bluetoothDevice = null; // Menyimpan perangkat Bluetooth
let printerCharacteristic = null; // Menyimpan karakteristik printer

async function connectToBluetoothPrinter(content) {
  try {
    if (!bluetoothDevice || !printerCharacteristic) {
      console.log("Belum terhubung ke printer, memulai koneksi...");

      // Memindai perangkat Bluetooth
      bluetoothDevice = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: [
          '49535343-fe7d-4ae5-8fa9-9fafd205e455' // UUID layanan yang valid
        ]
      });

      console.log("Perangkat dipilih:", bluetoothDevice.name);

      // Menghubungkan ke perangkat
      const server = await bluetoothDevice.gatt.connect();
      const service = await server.getPrimaryService('49535343-fe7d-4ae5-8fa9-9fafd205e455');
      printerCharacteristic = await service.getCharacteristic('49535343-8841-43f4-a8d4-ecbe34729bb3'); // Ganti dengan UUID karakteristik yang mendukung Write
    }

    console.log("Mengirim data ke printer...");
    const encoder = new TextEncoder();
    const data = encoder.encode(content + "\n\n");
    await printerCharacteristic.writeValue(data);

    alert("Berhasil mencetak melalui Bluetooth!");
  } catch (error) {
    console.error("Bluetooth Error:", error);
    alert("Gagal mencetak melalui Bluetooth.");
  }
}
