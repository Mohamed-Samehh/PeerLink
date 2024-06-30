## Setting Up Database Server: WampServer with PhpMyAdmin (MySQL)

1. **Run WampServer and Access PhpMyAdmin:**
   - Start WampServer.
   - Access PhpMyAdmin from WampServer.

2. **Login to PhpMyAdmin:**
   - Use Username `"root"` and leave the Password field empty.
   - Make sure Server choice is `"MySQL"`.

3. **Create Database:**
   - Create a new database named `"peerlink"` in PhpMyAdmin.

4. **Import Database File:**
   - Click on the `"Import"` tab in PhpMyAdmin.
   - Choose the file named `"peerlink.sql"` from the `"Data"` folder.
   - Execute the import to populate the `"peerlink"` database.

5. **Move Project Folder:**
   - Locate the folder named `"PeerLink_project"`.
   - Move it to `"C:\wamp64\www"` directory.

6. **Access Your Project:**
   - To run the project, open your web browser.
   - Enter the URL: [http://localhost/PeerLink_project/](http://localhost/PeerLink_project/).
