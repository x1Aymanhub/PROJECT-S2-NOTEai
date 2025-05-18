// Importation de dotenv pour gérer les variables d'environnement
import dotenv from 'dotenv';
dotenv.config();

// Configuration de l'API OpenRouter
const API_KEY = process.env.API_KEY;
const API_URL = process.env.API_URL;

// Options de modèle
const API_MODEL = process.env.API_MODEL;
const MAX_TOKENS = process.env.MAX_TOKENS;

// Exportation de la configuration
export {
    API_KEY,
    API_URL,
    API_MODEL,
    MAX_TOKENS
}; 