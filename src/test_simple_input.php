<!DOCTYPE html>
<html>
<head>
    <title>Simple Input Test</title>
</head>
<body style="padding: 50px; font-family: Arial;">
    <h1>Test Input Đơn Giản</h1>
    
    <h2>Test 1: Input cơ bản</h2>
    <input type="text" id="test1" placeholder="Gõ vào đây..." style="padding: 10px; width: 300px; font-size: 16px;">
    <p id="result1"></p>
    
    <h2>Test 2: Input giống search box</h2>
    <div style="display: flex; border: 2px solid #ec4899; border-radius: 25px; overflow: hidden; width: 400px;">
        <input type="text" id="test2" placeholder="Tìm kiếm..." style="border: none; padding: 10px 20px; flex: 1; font-size: 16px;">
        <button style="background: #ec4899; color: white; border: none; padding: 10px 20px; cursor: pointer;">Tìm</button>
    </div>
    <p id="result2"></p>
    
    <script>
        document.getElementById('test1').addEventListener('input', function() {
            document.getElementById('result1').textContent = 'Giá trị: ' + this.value;
            document.getElementById('result1').style.color = 'green';
        });
        
        document.getElementById('test2').addEventListener('input', function() {
            document.getElementById('result2').textContent = 'Tìm kiếm: ' + this.value;
            document.getElementById('result2').style.color = 'blue';
        });
        
        console.log('Test page loaded');
    </script>
</body>
</html>
