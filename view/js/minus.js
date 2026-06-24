function maiuscula(z){
    v = z.value.toUpperCase();
    z.value = v;
}

function minuscula(z){
    v = z.value.toLowerCase();
    z.value = v;
}

function capitalize(z){
    const capitalize = (str, lower = false) =>
        (lower ? str.toLowerCase() : str).replace(/(?:^|\s|["'([{])+\S/g, match => match.toUpperCase());
    ;
    return capitalize(z);
}
