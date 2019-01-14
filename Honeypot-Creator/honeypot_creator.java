/* This file is part of HIHAT v1.1
   ================================
   Copyright (c) 2007 HIHAT-Project                   

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 
*/

 
// import input -and output operations 
import java.io.*;


/* Honeypot-Creator: Transforms php-based web applications into a high-interaction web-based honeypot
                     Inserts all code contained in "insertionfile.txt" into each file of the source code, 
                     requires temporary write access in target directory of the web application
                     requires file insertionfile.txt            
                              
   Usage example: "java honeypot_creator /var/www/phpmyadmin/"                                          */ 
public class honeypot_creator {
    // counter for number of processed files
    public static int counter = 0;
    
    // file with code to insert at beginning of each filtered file
    private static String insertionFile = "insertionfile.txt";
   
   
    // empty constructor
    public honeypot_creator() {        
    }
    
    
    /* selects files to insert the code into
       @param filename : String with name of file 
       @returns        : true if code should be added + file should be considered (current setting: php files only) */    
    private static boolean filterOk( String filename ) {
        return ((filename.endsWith( ".php")) // | (filename.endsWith( "index.php")) |
                );  
    }
    
   /* lists all directories and processes each element, needs method "listContent"
      @param dir : File object of directory
      @returns   : void                       */  
   public static void listDir(File dir) {
   File[] files = dir.listFiles();
   if (files != null) {
      for (int i = 0; i < files.length; i++) {
         System.out.print(files[i].getAbsolutePath() + "\n");
         if (files[i].isDirectory()) {
            
            listDir(files[i]); 
            }
         else {
            if ( filterOk ( files[i].getAbsolutePath() ) )
              listContent( files[i].getAbsolutePath() );
         }
      }
    } else 
          System.out.print( "Directory not found! " );
    }
    
   
   /* inserts the content of the insertionfile into a single file of the web application
      @param fileName : String with name of file of web application
      @returns        : void                                                  */
   public static void listContent( String fileName ) {
       counter++;
       File sourcefile = new File (fileName );
    
       // temporary buffer file
       String tempFileString = "tempFileBuffer.txt";
           
        // main part
        try {
          BufferedInputStream in = new BufferedInputStream ( 
                                   new FileInputStream( sourcefile.getAbsolutePath() ));
          DataOutputStream out =      new DataOutputStream(
                                      new BufferedOutputStream(
                                      new FileOutputStream( tempFileString )));
          BufferedInputStream insertionIn = new BufferedInputStream ( 
                                     new FileInputStream( new File( insertionFile).getAbsolutePath() ));
                                                                       
          byte[] buf = new byte[4096];    
          int len;
          
          // write insertion into outputfile
          while ((len = insertionIn.read(buf)) > 0) {
            out.write(buf, 0, len);
          }            
          
          // append original data
          while ((len = in.read(buf)) > 0) {
            out.write(buf, 0, len);
          }
          out.close();
          in.close();    
           
          File newRenamedSourcefile = new File( sourcefile.getAbsolutePath() + "."
                        +  Math.abs(( new java.util.Random().nextInt() % 1000)) + "old" );
          
          if (!sourcefile.renameTo( newRenamedSourcefile ))
                    System.err.println( "Couldn't rename file!");      
           
           
          if ( ! (new File( tempFileString )).renameTo( new File( fileName))) {
              System.err.println( "Error: Couldn't create modified File! Data stored in " + sourcefile.getAbsolutePath());
          } else { 
                if (!newRenamedSourcefile.delete())
                    System.err.println( "Error: Couldn't delete renamed Sourcefile " + newRenamedSourcefile.getAbsolutePath());
            }
          } catch (IOException e) {
          System.err.println(e.toString());
        }
   }
    
    /* main program  
       usage example: "java honeypot_creator /var/www/phpmyadmin/"     */ 
    public static void main(String[] args) {         
      String directory;    
      // check arguments
      if ( args.length > 1 ) {                    
              System.out.println( "Too many parameters!" );
              System.exit( 0 );
      } else if ( args.length == 1 ) {
          // default directory
          if (args[ 0 ].equals("default" ))
              directory = "C:\\Dokumente und Einstellungen\\Michael Mustermann\\Eigene Dateien\\xampp\\htdocs\\phpmyadm"; //initial vaule for the directory - change for your settings
          else
              directory = args[ 0 ];             
                  
          System.out.println( "Processing " + args[ 0 ] + ":\n" );    
                  
          listDir( new File( directory ));
          //System.out.println( new File("C:\\Dokumente und Einstellungen\\Michael Mustermann\\Eigene Dateien\\xampp\\htdocs\\" + directory));          
          System.out.println( "\n" + counter + " files have been processed for directory " + directory );                
      } else 
          System.out.println( "Missing parameter: please add full directory path!" );
    }
    
}
