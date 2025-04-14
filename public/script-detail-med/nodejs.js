const fs = require('fs');
const path = require('path');

// Paths to files
const dataPath = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'file.json');
const templatePath = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'html-content.html');
const templatePath1 = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'html-content-precis.html');
const templatePath2 = path.join('/var/www/doctor.way-interactive-convergence.com/public/script-detail-med/', 'html-content-2.html');

const outputDir = path.join('/var/www/wic-doctor.com/WicDoctor/', 'medecin');
// Create output directory if it doesn't exist
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

// Load JSON data
const data = JSON.parse(fs.readFileSync(dataPath, 'utf-8'));

// Load HTML template
const template = fs.readFileSync(templatePath, 'utf-8');
const template1 = fs.readFileSync(templatePath1, 'utf-8');
const template2 = fs.readFileSync(templatePath2, 'utf-8');

// Function to dynamically build file path based on available data
function generatePage(entry) {
  console.log("entry: ", entry)
  let pageContent
  if(entry.type =="non-conventionné")
    {
      
  
   pageContent = template2
  if (entry.pays && entry.gouvernorat && (entry.specialities || entry.specialites))
  // Dynamically adjust all paths that start with ./../../
  {

    console.log("***1")
    pageContent = template2.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
      // Logic to dynamically replace ./../../ part
      let dynamicBasePath = './../../../..';
      return `${p1}="${dynamicBasePath}${p2}"`;
    });
    pageContent = template2.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
      // Logic to dynamically replace ./../../ part
      let dynamicBasePath = './../../../..';
      return `${p1}="${dynamicBasePath}${p2}"`;
    });
    
  }



  pageContent = pageContent
    .replace(/{{ id }}/g, entry.aleatoire)
    .replace(/{{ Nom }}/g, getDoctorName(entry.name))
    .replace(/{{ Ville }}/g, JSON.parse(entry.gouvernorat).fr)
    .replace(/{{ Spécialité }}/g, getDoctorSpec(entry.specialities, entry.specialites))




  }
  else{
    console.log("entry.availability_mode: ",entry.availability_mode)
    if(entry.availability_mode == "open")
   {
      pageContent = template
    if (entry.pays && entry.gouvernorat && (entry.specialities || entry.specialites))
    // Dynamically adjust all paths that start with ./../../
    {
  
      console.log("***1")
      pageContent = template.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
        // Logic to dynamically replace ./../../ part
        let dynamicBasePath = './../../../..';
        return `${p1}="${dynamicBasePath}${p2}"`;
      });
      pageContent = template.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
        // Logic to dynamically replace ./../../ part
        let dynamicBasePath = './../../../..';
        return `${p1}="${dynamicBasePath}${p2}"`;
      });
    }
  
  
  
    pageContent = pageContent
      .replace(/{{ id }}/g, entry.aleatoire)
      .replace(/{{ Nom }}/g, getDoctorName(entry.name))
      .replace(/{{ Ville }}/g, JSON.parse(entry.gouvernorat).fr)
      .replace(/{{ Spécialité }}/g, getDoctorSpec(entry.specialities, entry.specialites))
  }
  else
  {
    console.log("entry.availability_mode: ",entry.availability_mode)
    pageContent = template1
    if (entry.pays && entry.gouvernorat && (entry.specialities || entry.specialites))
    // Dynamically adjust all paths that start with ./../../
    {
  
      console.log("***1")
      pageContent = template1.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
        // Logic to dynamically replace ./../../ part
        let dynamicBasePath = './../../../..';
        return `${p1}="${dynamicBasePath}${p2}"`;
      });
      pageContent = template1.replace(/(href|src)="\.\/\.\.\/\.\.(\/[^\s"]*)"/g, function (match, p1, p2) {
        // Logic to dynamically replace ./../../ part
        let dynamicBasePath = './../../../..';
        return `${p1}="${dynamicBasePath}${p2}"`;
      });
    }
  
  
  
    pageContent = pageContent
      .replace(/{{ id }}/g, entry.aleatoire)
      .replace(/{{ Nom }}/g, getDoctorName(entry.name))
      .replace(/{{ Ville }}/g, JSON.parse(entry.gouvernorat).fr)
      .replace(/{{ Spécialité }}/g, getDoctorSpec(entry.specialities, entry.specialites))
  }
  
  }


  // Build path segments based on available fields
  let pathSegments = [];
  function getDoctorName(name) {
    console.log("*************************************************")
    // Vérifiez si `name` est une chaîne JSON
    if (typeof name === 'string' && name.trim().startsWith('{') && name.trim().endsWith('}')) {
      try {
        const parsedName = JSON.parse(name); // Tentez de parser le JSON
        return JSON.parse(name).fr; // Vérifiez si la clé 'fr' existe
      } catch (error) {
        console.error("Erreur lors du parsing :", error);
      }
    }
    return name; // Retourne false si ce n'est pas un JSON valide
  }
  function getDoctorSpec(name1, name2) {
    console.log("*************************************************: ", name1, name2)
    console.log("!name1: ", !name1)
    if (!name1) {
      return name2; // Retourne false si ce n'est pas un JSON valide

    }
    // Vérifiez si `name` est une chaîne JSON
    else if (JSON.stringify(name1).trim().startsWith('[') && JSON.stringify(name1).trim().endsWith(']')) {
      console.log("naaaaaaaaaaaaaaaaaaaaaaaaaaame")
      try {
        console.log("JSON.parse(data[0].name).fr: ", JSON.parse(name1[0].name).fr)
        return JSON.parse(name1[0].name).fr; // Vérifiez si la clé 'fr' existe
      } catch (error) {
        console.error("Erreur lors du parsing :", error);
      }
    }
  }
  //pathSegments.push(getDoctorName(entry.name).replace(/\s+/g, '-').toLowerCase());
  // Build path segments based on available fields
  pathSegments = [];
  if (entry.pays) pathSegments.push(JSON.parse(entry.pays).fr.replace(/\s+/g, '-').toLowerCase());
  if (entry.gouvernorat) pathSegments.push(JSON.parse(entry.gouvernorat).fr.replace(/\s+/g, '-').toLowerCase());
  if (entry.specialities || entry.specialites) pathSegments.push(getDoctorSpec(entry.specialities, entry.specialites).replace(/\s+/g, '-').toLowerCase());
  //pathSegments.push(entry.aleatoire);
  pathSegments.push(`dr-${getDoctorName(entry.name).replace(/\s+/g, '-').toLowerCase()}`);
console.log("pathSegments: ",pathSegments)
  // Combine path segments to form the filename
  const filename = `${pathSegments.join('/')}-${entry.aleatoire}.html`;
  const filePath = path.join(outputDir, filename);

  // Ensure the directory for the category exists
  const directory = path.dirname(filePath);
  if (!fs.existsSync(directory)) {
    fs.mkdirSync(directory, { recursive: true });
  }

  // Write the unchanged template to file
  fs.writeFileSync(filePath, pageContent, 'utf-8');
  console.log(`Generated page: ${filePath}`);
}

// Generate HTML pages for each entry without changing template content
data.forEach(generatePage);

console.log('HTML page generation completed.');
