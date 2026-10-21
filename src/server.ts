import express from 'express';
import mysql from 'mysql2/promise';
const app = express();
app.use(express.json());
app.get('/api/health', async (_req, res) => {
  try {
    const conn = await mysql.createConnection({host:process.env.DB_HOST ?? 'mysql',user:process.env.DB_USER ?? 'originie_app',password:process.env.DB_PASSWORD,database:process.env.DB_NAME ?? 'originie'});
    await conn.query('SELECT 1'); await conn.end();
    res.json({api:'ok',banco:'ok'});
  } catch { res.status(503).json({api:'ok',banco:'indisponivel'}); }
});
app.listen(Number(process.env.PORT ?? 3000),'0.0.0.0',()=>console.log('API Originie iniciada'));
