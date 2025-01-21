const fs = require('fs');
const path = require('path');

// Paths to files
const dataPath = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'file.json');
const templateConventioned = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'html-content.html');
const templateNonConventioned = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'html-content-2.html');
const outputDir = path.join('/var/www/wic-doctor.com/WicDoctor/', 'medecin');

// Create output directory if it doesn't exist
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

// Check if file.json exists and is not empty
if (!fs.existsSync(dataPath)) {
    console.error('Fichier JSON introuvable.');
    process.exit(1);
}

const fileContent = fs.readFileSync(dataPath, 'utf-8');
if (!fileContent.trim()) {
    console.error('Fichier JSON vide.');
    process.exit(1);
}

const data = JSON.parse(fileContent);

// Function to parse and validate JSON strings
function parseJSONField(field) {
    if (typeof field === 'string' && field.trim().startsWith('{') && field.trim().endsWith('}')) {
        try {
            return JSON.parse(field);
        } catch (error) {
            console.error(`Erreur lors du parsing JSON pour le champ : ${field}`, error);
        }
    }
    return field;
}

// Helper to safely get doctor's name
function getDoctorName(name) {
    const parsedName = parseJSONField(name);
    return parsedName?.fr || name || 'inconnu';
}

// Helper to safely get doctor's specialty
function getDoctorSpec(specialities, specialites) {
    const primarySpec = specialities?.[0]?.name || specialites?.[0]?.name;
    const parsedSpec = parseJSONField(primarySpec);
    return parsedSpec?.fr || primarySpec || 'inconnue';
}

// Helper to safely get a value or default
function getValueOrDefault(value, defaultValue = 'inconnu') {
    const parsedValue = parseJSONField(value);
    return parsedValue?.fr || defaultValue;
}

// Replace relative paths dynamically
function replaceRelativePaths(templateContent) {
    return templateContent.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
        let dynamicBasePath = './../../../..'; // Adjust this base path dynamically if necessary
        return `${p1}="${dynamicBasePath}${p2}"`;
    });
}

// Generate the HTML pages for each doctor entry
data.forEach(entry => {
    console.log('Traitement de l\'entrée :', entry);

    // Choose the correct template based on the type
    const templatePath = entry.type === 'conventionné' ? templateConventioned : templateNonConventioned;
    if (!fs.existsSync(templatePath)) {
        console.error(`Template introuvable : ${templatePath}`);
        return;
    }
    let pageContent = fs.readFileSync(templatePath, 'utf-8');

    // Build path segments based on available fields
    const pathSegments = [];
    const pays = getValueOrDefault(entry.pays, 'inconnu');
    const gouvernorat = getValueOrDefault(entry.gouvernorat, 'inconnu');
    const specialty = getDoctorSpec(entry.specialities, entry.specialites);

    // Add segments to path
    pathSegments.push(
        pays.replace(/\s+/g, '-').toLowerCase(),
        gouvernorat.replace(/\s+/g, '-').toLowerCase(),
        specialty.replace(/\s+/g, '-').toLowerCase(),
        `dr-${getDoctorName(entry.name).replace(/\s+/g, '-').toLowerCase()}-${entry.aleatoire}.html`
    );

    console.log("Segments de chemin : ", pathSegments);

    // Combine path segments to form the full path
    const filePath = path.join(outputDir, ...pathSegments);

    // Ensure the directory for the category exists
    const directory = path.dirname(filePath);
    if (!fs.existsSync(directory)) {
        fs.mkdirSync(directory, { recursive: true });
    }

    // Replace relative paths in the template
    pageContent = replaceRelativePaths(pageContent);

    // Replace placeholders in the template
    pageContent = pageContent
        .replace(/{{ id }}/g, entry.aleatoire)
        .replace(/{{ Nom }}/g, getDoctorName(entry.name))
        .replace(/{{ Ville }}/g, gouvernorat)
        .replace(/{{ Spécialité }}/g, specialty);

    // Write the HTML file
    fs.writeFileSync(filePath, pageContent, 'utf-8');
    console.log(`Page générée : ${filePath}`);
});

console.log('Génération des pages HTML terminée.');
