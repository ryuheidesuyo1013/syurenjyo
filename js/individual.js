function calculateUniqueBMI() {
    const weightInput = document.getElementById('weight');
    const goalToWeek = document.getElementById('goal-to-week');
    const whatWeightWeek = document.getElementById('what-weight-week');
    const goalToMonth = document.getElementById('goal-to-month');
    const whatWeightMonth = document.getElementById('what-weight-month');
    const errorLog = document.getElementById('error-log');

    const currentWeight = parseFloat(weightInput.value);

    if (isNaN(currentWeight) || currentWeight <= 0) {
        errorLog.textContent = "正しい体重を入力してください。";
        goalToWeek.innerHTML = "";
        whatWeightWeek.innerHTML = "";
        goalToMonth.innerHTML = "";
        whatWeightMonth.innerHTML = "";
        return;
    }

    errorLog.textContent = "";

    const weeklyGoalMin = currentWeight * 1.0025;
    const weeklyGoalMax = currentWeight * 1.005;
    const weeklyWeightMin = weeklyGoalMin - currentWeight;
    const weeklyWeightMax = weeklyGoalMax - currentWeight;
    const monthlyGoalMin = currentWeight + weeklyWeightMin * 4;
    const monthlyGoalMax = currentWeight + weeklyWeightMax * 4;
    const monthlyWeightMin = monthlyGoalMin - currentWeight;
    const monthlyWeightMax = monthlyGoalMax - currentWeight;

    goalToWeek.innerHTML = `<span class="red large">1週間</span>後の目標体重: <span class="red large">${weeklyGoalMin.toFixed(2)}</span>kg ~ <span class="red large">${weeklyGoalMax.toFixed(2)}</span>kg`;
    whatWeightWeek.innerHTML = `<span class="red large">1週間</span>であと: <span class="red large">${weeklyWeightMin.toFixed(2)}</span>kg ~ <span class="red large">${weeklyWeightMax.toFixed(2)}</span>kg増やす`;

    goalToMonth.innerHTML = `<span class="blue large">1か月</span>後の目標体重: <span class="blue large">${monthlyGoalMin.toFixed(2)}</span>kg ~ <span class="blue large">${monthlyGoalMax.toFixed(2)}</span>kg`;
    whatWeightMonth.innerHTML = `<span class="blue large">1か月</span>であと: <span class="blue large">${monthlyWeightMin.toFixed(2)}</span>kg ~ <span class="blue large">${monthlyWeightMax.toFixed(2)}</span>kg増やす`;
}
