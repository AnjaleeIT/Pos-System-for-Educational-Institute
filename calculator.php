<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Calculator</title>
    <style>
        :root {
            --bg-color: #f1f5f9;
            --calc-bg: #ffffff;
            --text-color: #1e293b;
            --btn-bg: #f8fafc;
            --btn-hover: #e2e8f0;
            --primary-btn: #3b82f6;
            --operator-btn: #f59e0b;
            --danger-btn: #ef4444;
        }

        body { 
            background-color: var(--bg-color);
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; 
        }
        
        .calculator {
            width: 340px;
            background: var(--calc-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.21), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }

        .display {
            background: #f8fafc;
            padding: 25px;
            border-radius: 20px;
            text-align: right;
            margin-bottom: 25px;
            color: var(--text-color);
            border: 1px solid #e2e8f0;
        }

        #result { font-size: 32px; font-weight: 700; word-wrap: break-word; color: #3b5374; }

        .buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        button {
            padding: 18px 0;
            border: none;
            border-radius: 15px;
            background: var(--btn-bg);
            color: var(--text-color);
            font-size: 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        button:hover { background: var(--btn-hover); }
        button:active { transform: scale(0.96); }

        .operator { color: var(--operator-btn); font-weight: bold; }
        .equals { background: var(--primary-btn); color: white; grid-row: span 2; }
        .equals:hover { background: #2563eb; }
        .danger { color: var(--danger-btn); }

    </style>
</head>
<body>

<div class="calculator">
    <div class="display">
        <div id="result">0</div>
    </div>
    <div class="buttons">
        <button onclick="clearDisplay()" class="danger">AC</button>
        <button onclick="deleteLast()">DEL</button>
        <button class="operator" onclick="appendOperator('/')">/</button>
        <button class="operator" onclick="appendOperator('*')">×</button>
        
        <button onclick="appendNumber('7')">7</button>
        <button onclick="appendNumber('8')">8</button>
        <button onclick="appendNumber('9')">9</button>
        <button class="operator" onclick="appendOperator('-')">-</button>
        
        <button onclick="appendNumber('4')">4</button>
        <button onclick="appendNumber('5')">5</button>
        <button onclick="appendNumber('6')">6</button>
        <button class="operator" onclick="appendOperator('+')">+</button>
        
        <button onclick="appendNumber('1')">1</button>
        <button onclick="appendNumber('2')">2</button>
        <button onclick="appendNumber('3')">3</button>
        <button class="equals" onclick="calculate()">=</button>
        
        <button onclick="appendNumber('%')">%</button>
        <button onclick="appendNumber('0')">0</button>
        <button onclick="appendNumber('.')">.</button>
    </div>
</div>

<script>
    let currentInput = "";
    const display = document.getElementById("result");

    function updateDisplay() {
        display.innerText = currentInput === "" ? "0" : currentInput;
    }

    function appendNumber(num) {
        currentInput += num;
        updateDisplay();
    }

    function appendOperator(op) {
        if (currentInput === "") return;
        currentInput += op;
        updateDisplay();
    }

    function clearDisplay() {
        currentInput = "";
        updateDisplay();
    }

    function deleteLast() {
        currentInput = currentInput.slice(0, -1);
        updateDisplay();
    }

    function calculate() {
        try {
            if (currentInput === "") return;
            currentInput = eval(currentInput).toString();
            updateDisplay();
        } catch {
            display.innerText = "Error";
            currentInput = "";
        }
    }
</script>

</body>
</html>