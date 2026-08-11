<?php
defined('ABSPATH') || exit;
class Tersuite_AI_Zip_Packager {
    private function safe_path($path){
        if(!is_string($path) || $path==='' || strpos($path,"\0")!==false) return false;
        $path=str_replace('\\','/',$path); if($path[0]==='/' || preg_match('/^[A-Za-z]:\\//',$path)) return false;
        $parts=explode('/',$path); foreach($parts as $part){ if($part===''||$part==='.') continue; if($part==='..') return false; }
        return ltrim($path,'/');
    }
    public function package($manifest){
        if(!class_exists('ZipArchive')) return new WP_Error('zip_missing',__('ZipArchive is required.','tersuite-ai-studio'));
        $files=isset($manifest['files'])&&is_array($manifest['files'])?$manifest['files']:array(); if(!$files) return new WP_Error('empty_manifest',__('The delivery contains no files.','tersuite-ai-studio'));
        $tmp=wp_tempnam('tersuite-plugin'); if(!$tmp) return new WP_Error('temp_failed',__('Could not create temporary package.','tersuite-ai-studio')); @unlink($tmp);
        $root=trailingslashit($tmp).'package'; wp_mkdir_p($root); $zipfile=trailingslashit($tmp).'plugin.zip';
        foreach($files as $f){ $path=$this->safe_path(isset($f['path'])?$f['path']:''); if($path===false) continue; $target=$root.'/'.$path; wp_mkdir_p(dirname($target)); $content=isset($f['content'])?$f['content']:''; if(!is_string($content)) $content=wp_json_encode($content); file_put_contents($target,$content); }
        $zip=new ZipArchive(); if($zip->open($zipfile,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true){ return new WP_Error('zip_open',__('Could not create ZIP.','tersuite-ai-studio')); }
        $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS)); foreach($iterator as $file){ if($file->isFile()){ $local=substr($file->getPathname(),strlen($root)+1); $zip->addFile($file->getPathname(),str_replace('\\','/',$local)); } } $zip->close();
        return array('path'=>$zipfile,'root'=>$root,'temp'=>$tmp);
    }
}
