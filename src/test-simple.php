<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Simple Modal</title>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
        }
        
        .modal-overlay.active {
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        
        .product-modal {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .test-btn {
            padding: 15px 30px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px;
        }
    </style>
</head>
<body>
    <h1>Test Modal - Click button below</h1>
    <button class="test-btn" onclick="testOpen()">OPEN MODAL</button>
    
    <div id="productModal" class="modal-overlay">
        <div class="product-modal">
            <button class="modal-close" onclick="testClose()">CLOSE X</button>
            <h2>Modal Content Here</h2>
            <p>This is a test modal. If you see this, the modal is working!</p>
            <img src="https://via.placeholder.com/300" alt="Test">
        </div>
    </div>
    
    <script>
        function testOpen() {
            console.log('Opening modal...');
            const modal = document.getElementById('productModal');
            console.log('Modal element:', modal);
            
            if (!modal) {
                alert('ERROR: Modal not found!');
                return;
            }
            
            modal.classList.add('active');
            modal.style.display = 'flex';
            console.log('Modal classes:', modal.className);
            console.log('Modal display:', modal.style.display);
        }
        
        function testClose() {
            console.log('Closing modal...');
            const modal = document.getElementById('productModal');
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
        
        console.log('Script loaded');
    </script>
</body>
</html>
