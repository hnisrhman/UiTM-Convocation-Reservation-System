function updateTotal() {
    let robePrice = 0, capPrice = 0, hoodPrice = 0;

    let robeType = document.getElementById("robeType").value;
    let cap = document.getElementById("gradCap").value;
    let hoodCode = document.getElementById("hoodCode").value;
    let oneSet = document.getElementById("oneSet").checked;

    // Robe pricing
    if (robeType === "Diploma") robePrice = 15;
    else if (robeType === "Degree") robePrice = 25;
    else if (robeType === "Master") robePrice = 35;
    else if (robeType === "PhD") robePrice = 40;

    // Cap pricing
    if (cap === "Mortar Board") capPrice = 10;
    else if (cap === "Bonnet") capPrice = 15;

    // Hood pricing
    if (hoodCode.trim() !== "") hoodPrice = 10;

    let total = robePrice + capPrice + hoodPrice;

    // Apply One Set Package price for all including PhD
    if (oneSet) {
        if (robeType === "Diploma") total = 30;
        else if (robeType === "Degree") total = 40;
        else if (robeType === "Master") total = 50;
        else if (robeType === "PhD") total = 60; // correct as you asked ✅
    }

    document.getElementById("totalPrice").value = total.toFixed(2);
}

function toggleOneSet() {
    updateTotal();
}

function calculateTotal() {
    let robeType = document.getElementById("robeType").value;
    if (robeType === "") {
        alert("Please select your Robe Type before submitting.");
        return false;
    }
    return true;
}
