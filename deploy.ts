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
    // Use relative path from current FTP directory (use . for root)
    const remotePath = remoteDir === '.' 
      ? entry.name 
      : `${remoteDir}/${entry.name}`;

    if (entry.isDirectory()) {
      console.log(`📁 Creating directory: ${remotePath}`);
      try {
        await client.ensureDir(remotePath);
        await client.cd('/');  // Reset to root after ensureDir changes cwd
        await client.cd(REMOTE_DIR);  // Go back to our target directory
      } catch (err) {
        console.log(`   Directory may already exist, continuing...`);
      }
      await uploadDirectory(client, localPath, remotePath);
    } else {
      const displayLocal = localPath.replace(/\\/g, '/');
      console.log(`📤 Uploading: ${displayLocal} -> ${remotePath}`);
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

    // Use passive mode (required by many shared hosting providers)
    client.ftp.socket.setTimeout(30000);

    console.log('✅ Connected to FTP server');
    
    // Debug: show current directory and list its contents
    const pwd = await client.pwd();
    console.log(`📍 Current FTP directory: ${pwd}`);
    
    const rootList = await client.list();
    console.log('📋 Root directory contents:');
    for (const item of rootList) {
      const type = item.isDirectory ? '📁' : '📄';
      console.log(`   ${type} ${item.name}`);
    }

    // Ensure remote base directory exists and cd into it
    try {
      await client.cd(REMOTE_DIR);
      console.log(`📍 Changed to: ${await client.pwd()}`);
    } catch (err) {
      console.log(`❌ Could not cd to ${REMOTE_DIR}`);
      throw err;
    }
    
    if (DEBUG) {
      console.log(`🔍 DEBUG MODE: Listing contents...`);
      const list = await client.list();
      console.log('\n📋 Directory contents:');
      for (const item of list) {
        const type = item.isDirectory ? '📁' : '📄';
        console.log(`${type} ${item.name} (${item.size} bytes)`);
      }
      console.log('\n⚠️  No files were uploaded (DEBUG=true)');
    } else {
      console.log(`📂 Uploading ${LOCAL_DIR} to ${REMOTE_DIR}...`);
      // Upload starting from current directory (.)
      await uploadDirectory(client, LOCAL_DIR, '.');
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
