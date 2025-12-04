import { createWriteStream } from 'fs';
import { readdir, stat } from 'fs/promises';
import { join, relative } from 'path';
import { Client } from 'basic-ftp';
import * as dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Set to true to list remote directory contents instead of uploading
const DEBUG = false;

const FTP_HOST = process.env.FTP_HOST;
const FTP_PORT = parseInt(process.env.FTP_PORT || '21');
const FTP_USER = process.env.FTP_USER;
const FTP_PASSWORD = process.env.FTP_PASSWORD;
const DESTINATION_PATH = process.env.DESTINATION_PATH || '/';

if (!FTP_HOST || !FTP_USER || !FTP_PASSWORD) {
  console.error('❌ Missing FTP configuration in .env file');
  console.error('Required: FTP_HOST, FTP_USER, FTP_PASSWORD');
  process.exit(1);
}

const LOCAL_DIR = './dist';
const REMOTE_DIR = DESTINATION_PATH;

async function uploadDirectory(client: Client, localDir: string, remoteDir: string) {
  const entries = await readdir(localDir, { withFileTypes: true });

  for (const entry of entries) {
    const localPath = join(localDir, entry.name);
    const remotePath = `${remoteDir}/${entry.name}`;

    if (entry.isDirectory()) {
      console.log(`📁 Creating directory: ${remotePath}`);
      try {
        await client.ensureDir(remotePath);
      } catch (err) {
        console.log(`   Directory may already exist, continuing...`);
      }
      await uploadDirectory(client, localPath, remotePath);
    } else {
      console.log(`📤 Uploading: ${localPath} -> ${remotePath}`);
      await client.uploadFrom(localPath, remotePath);
    }
  }
}

async function deploy() {
  const client = new Client();
  client.ftp.verbose = false;

  try {
    console.log(`🔌 Connecting to ${FTP_HOST}:${FTP_PORT}...`);
    console.log(`📍 Destination path: ${REMOTE_DIR}`);
    await client.access({
      host: FTP_HOST,
      port: FTP_PORT,
      user: FTP_USER,
      password: FTP_PASSWORD,
      secure: false,
    });

    console.log('✅ Connected to FTP server');
    
    if (DEBUG) {
      console.log(`🔍 DEBUG MODE: Listing contents of ${REMOTE_DIR}...`);
      const list = await client.list(REMOTE_DIR);
      console.log('\n📋 Remote directory contents:');
      for (const item of list) {
        const type = item.isDirectory ? '📁' : '📄';
        console.log(`${type} ${item.name} (${item.size} bytes)`);
      }
      console.log('\n⚠️  No files were uploaded (DEBUG=true)');
    } else {
      console.log(`📂 Uploading ${LOCAL_DIR} to ${REMOTE_DIR}...`);
      await uploadDirectory(client, LOCAL_DIR, REMOTE_DIR);
      console.log('✅ Deployment complete!');
    }

  } catch (err) {
    console.error('❌ Deployment failed:', err);
    process.exit(1);
  } finally {
    client.close();
  }
}

deploy();
