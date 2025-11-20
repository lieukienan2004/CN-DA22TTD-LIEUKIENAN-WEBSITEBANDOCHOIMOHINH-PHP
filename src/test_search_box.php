<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Search Box</title>
    <link rel="stylesheet" href="assets/css/search-simple.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 50px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            min-height: 30px;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <h1>🔍 Test Search Box</h1>
    
    <div class="test-section">
        <h2>Search Box Đơn Giản (Không có hiệu ứng)</h2>
        <div class="search-box-simple">
            <i class="fas fa-search search-icon-simple"></i>
            <input type="text" id="searchSimple" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
            <button class="search-btn-simple" onclick="testSearch()">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="result" id="resultSimple">Chưa có gì...</div>
    </div>
    
    <div class="test-section">
        <h2>Kiểm tra:</h2>
        <ul id="checks">
            <li>⏳ Đang kiểm tra...</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>Hướng dẫn:</h2>
        <ol>
            <li>Thử click vào ô tìm kiếm</li>
            <li>Thử gõ chữ vào</li>
            <li>Nhấn Enter hoặc click nút tìm kiếm</li>
            <li>Nếu hoạt động tốt, tôi sẽ áp dụng vào trang thật</li>
        </ol>
    </div>
    
    <script>
        const input = document.getElementById('searchSimple');
        const result = document.getElementById('resultSimple');
        const checks = document.getElementById('checks');
        
        // Test input
        input.addEventListener('focus', function() {
            console.log('✓ Input focused');
        });
        
        input.addEventListener('input', function() {
            result.textContent = '✓ Đang gõ: "' + this.value + '"';
            result.className = 'result success';
            console.log('Input value:', this.value);
        });
        
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                testSearch();
            }
        });
        
        function testSearch() {
            const value = input.value.trim();
            if (value) {
                result.textContent = '✓ Tìm kiếm: "' + value + '"';
                result.className = 'result success';
                alert('Sẽ tìm kiếm: ' + value);
                // window.location.href = 'products.php?search=' + encodeURIComponent(value);
            } else {
                result.textContent = '✗ Vui lòng nhập từ khóa';
                result.style.background = '#fee2e2';
                result.style.color = '#991b1b';
            }
        }
        
        // Run checks
        setTimeout(() => {
            let checkResults = [];
            
            checkResults.push('✓ Input element found: ' + (input ? 'Yes' : 'No'));
            checkResults.push('✓ Input disabled: ' + input.disabled);
            checkResults.push('✓ Input readonly: ' + input.readOnly);
            checkResults.push('✓ Input type: ' + input.type);
            
            const style = window.getComputedStyle(input);
            checkResults.push('✓ Pointer events: ' + style.pointerEvents);
            checkResults.push('✓ Cursor: ' + style.cursor);
            checkResults.push('✓ Z-index: ' + style.zIndex);
            
            checks.innerHTML = checkResults.map(c => '<li>' + c + '</li>').join('');
        }, 500);
        
        console.log('Test page loaded');
        console.log('Input element:', input);
    </script>
</body>
</html>
