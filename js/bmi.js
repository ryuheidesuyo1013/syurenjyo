function calculateUniqueBMI() {
    const height = document.getElementById("height").value;
    const weight = document.getElementById("weight").value;
    const position = document.getElementById("position").value;
    const age = document.getElementById("age").value;

    const errorMessageElement = document.getElementById("errorMessage");
    const resultElement = document.getElementById("bmiResult");
    const weightToGoalElement = document.getElementById("weightToGoal");

    if (!height || !weight || !position || isNaN(height) || isNaN(weight)) {
        errorMessageElement.innerText = "すべての項目を正しく入力してください。";
        errorMessageElement.style.display = "block";
        resultElement.innerHTML = "";
        weightToGoalElement.innerHTML = "";
        return;
    } else {
        errorMessageElement.style.display = "none";
    }

    const bmiTable = {
        "13F": { DF: 19.0, GK: 20.5 },
        "14F": { DF: 20.0, GK: 21.5 },
        "13": { DF: 19.5, GK: 20.5 },
        "14": { DF: 20.5, GK: 21.5 },
        "15": { DF: 21.0, GK: 21.5 },
        "16": { DF: 22.0, GK: 22.5 },
        "17": { DF: 22.0, GK: 23.0 },
        "18": { DF: 22.5, GK: 23.5 },
        "19": { DF: 23.0, GK: 23.5 },
        "20": { DF: 23.5, GK: 24.0 }
    };

    let targetBmi = 0;

    // "F"付きの年齢を処理する
    if (age === "13F" || age === "14F") {
        if (position === "GK") {
            // 13F, 14FのGKの目標BMIを設定
            targetBmi = bmiTable[age] ? bmiTable[age].GK : 0;
        } else {
            // 13F, 14FのDF, MF, FWの目標BMIを設定
            targetBmi = bmiTable[age] ? bmiTable[age].DF : 0;
        }
    } else if (position === "GK") {
        targetBmi = bmiTable[age] ? bmiTable[age].GK : 0;  // GKの場合
    } else {
        targetBmi = bmiTable[age] ? bmiTable[age].DF : 0;  // DF, MF, FWの場合
    }
    
    const bmi = weight / Math.pow(height / 100, 2);
    const weightToReachTarget = (targetBmi * Math.pow(height / 100, 2)) - weight;

    resultElement.innerHTML = `
        現在のBMI: <span class="bmi-result">${bmi.toFixed(1)}</span><br>
        目標BMI: <span class="bmi-result">${targetBmi}</span>`;
    
    weightToGoalElement.innerHTML = weightToReachTarget > 0
        ? `目標BMIを達成するためには、<span class="bmi-result">${weightToReachTarget.toFixed(1)}kg</span> 増量が必要です。`
        : `目標BMIを達成しています！`;
}
document.addEventListener("scroll", function () {
    const scrollY = window.scrollY;
    document.body.style.backgroundPositionY = `${scrollY * 0}px`;
});