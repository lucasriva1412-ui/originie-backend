
import mysql from "mysql2/promise";

const banco = mysql.createPool({
  host: process.env.DB_HOST ?? "127.0.0.1",
  port: Number(process.env.DB_PORT ?? 3307),
  user: process.env.DB_USER ?? "originie_app",
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME ?? "originie",
  waitForConnections: true,
  connectionLimit: 10
});

export default banco;
