<?php

// from http://tools.ietf.org/html/draft-ietf-secsh-filexfer-00
define('SSH_FXP_INIT', 1);
define('SSH_FXP_VERSION', 2);
define('SSH_FXP_OPEN', 3);
define('SSH_FXP_CLOSE', 4);
define('SSH_FXP_READ', 5);
define('SSH_FXP_WRITE', 6);
define('SSH_FXP_LSTAT', 7);
define('SSH_FXP_FSTAT', 8);
define('SSH_FXP_SETSTAT', 9);
define('SSH_FXP_FSETSTAT', 10);
define('SSH_FXP_OPENDIR', 11);
define('SSH_FXP_READDIR', 12);
define('SSH_FXP_REMOVE', 13);
define('SSH_FXP_MKDIR', 14);
define('SSH_FXP_RMDIR', 15);
define('SSH_FXP_REALPATH', 16);
define('SSH_FXP_STAT', 17);
define('SSH_FXP_RENAME', 18);
define('SSH_FXP_STATUS', 101);
define('SSH_FXP_HANDLE', 102);
define('SSH_FXP_DATA', 103);
define('SSH_FXP_NAME', 104);
define('SSH_FXP_ATTRS', 105);
define('SSH_FXP_EXTENDED', 200);
define('SSH_FXP_EXTENDED_REPLY', 201);

define('SSH_FILEXFER_ATTR_SIZE', 0x00000001);
define('SSH_FILEXFER_ATTR_UIDGID', 0x00000002);
define('SSH_FILEXFER_ATTR_PERMISSIONS', 0x00000004);
define('SSH_FILEXFER_ATTR_ACMODTIME', 0x00000008);
define('SSH_FILEXFER_ATTR_EXTENDED', 0x80000000);

define('SSH_FX_OK', 0);
define('SSH_FX_EOF', 1);
define('SSH_FX_NO_SUCH_FILE', 2);
define('SSH_FX_PERMISSION_DENIED', 3);
define('SSH_FX_FAILURE', 4);
define('SSH_FX_BAD_MESSAGE', 5);
define('SSH_FX_NO_CONNECTION', 6);
define('SSH_FX_CONNECTION_LOST', 7);
define('SSH_FX_OP_UNSUPPORTED', 8);

define('SSH_FXF_READ', 0x00000001);
define('SSH_FXF_WRITE', 0x00000002);
define('SSH_FXF_APPEND', 0x00000004);
define('SSH_FXF_CREAT', 0x00000008);
define('SSH_FXF_TRUNC', 0x00000010);
define('SSH_FXF_EXCL', 0x00000020);



class SFTPServer
{
    protected $_handle_serial = 1;
    protected $_handle_infos = array();

    public function logger($message)
    {
        file_put_contents('/tmp/sftp-log', $message . "\n", FILE_APPEND);
    }

    public function getPermWord($perms)
    {
        // From http://tw2.php.net/manual/en/function.fileperms.php
        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Symbolic Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // Directory
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        } else {
            // Unknown
            $info = 'u';
        }

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }

    public function getRealPath($project, $project_path)
    {
        return "/srv/project_data/{$project}{$project_path}";
    }

    public function read()
    {
        while (true) {
            if (feof($this->fp)) {
                return false;
            }
            // 先取得 4 個 byte 的 packet length
            $length = fread($this->fp, 4);
            if ('' !== $length) {
                break;
            }
            usleep(1000);
        }
        $array = unpack('NLength', $length);
        $length = $array['Length'];
        if (!$length) {
            exit;
        }
        // 再來把資料取出來
        $packet = fread($this->fp, 1);
        $array = unpack('CType', $packet);
        $length = $length - 1;
        $data = '';

        while ($length > strlen($data)) {
            $data .= fread($this->fp, $length - strlen($data));
        }
        return array($array['Type'], $data);
    }

    public function send($packet_type, $datas)
    {
        $length = 1;
        $args = func_get_args();
        for ($i = 1; $i < count($args); $i ++) {
            $length += strlen($args[$i]);
        }
        $ret = pack('NC', $length, $packet_type);
        for ($i = 1; $i < count($args); $i ++) {
            $ret .= $args[$i];
        }
        fwrite($this->output, $ret);
    }

    public function parsePath($path)
    {
        if ($path == '/') {
            return array(null, '/');
        }

        $terms = explode('/', trim($path, '/'));
        $project = array_shift($terms);
        if (!in_array($project, $this->getProjectsByUser())) {
            throw new Exception('project not found', 404);
        }
        return array($project, '/' . implode('/', $terms));
    }

    public function getFileSize($project, $path)
    {
        return filesize($this->getRealPath($project, $path));
    }

    public function getFilePermission($project, $path)
    {
        return fileperms($this->getRealPath($project, $path));
    }

    public function getFileTime($project, $path)
    {
        $path = $this->getRealPath($project, $path);
        return array(fileatime($path), filemtime($path));
    }

    public function getFTPAbsolutePath($base, $dir)
    {
        if ($dir[0] == '/') {
            $terms = array();
        } else {
            $terms = ($base == '/') ? array() : explode('/', trim($base, '/'));
        }

        foreach (explode('/', trim($dir, '/')) as $term) {
            if ('.' == $term) {
                continue;
            }
            if ('..' == $term) {
                array_pop($terms);
                continue;
            }
            $terms[] = $term;
        }
        return '/' . implode('/', $terms);
    }

    public function parseAttrs($attrs)
    {
        $ret = array();
        $r = unpack('Nflags', $attrs);
        $attrs = substr($attrs, 4);

        $ret = array_merge($ret, $r);
        $flags = $r['flags'];

        if ($flags & SSH_FILEXFER_ATTR_SIZE) {
            $r = unpack('Nsize', $attrs);
            $attrs = substr($attrs, 4);
            $ret = array_merge($ret, $r);
        }

        if ($flags & SSH_FILEXFER_ATTR_UIDGID) {
            $r = unpack('Nuid/Ngid', $attrs);
            $attrs = substr($attrs, 8);
            $ret = array_merge($ret, $r);
        }

        if ($flags & SSH_FILEXFER_ATTR_PERMISSIONS) {
            $r = unpack('Npermissions', $attrs);
            $attrs = substr($attrs, 4);
            $ret = array_merge($ret, $r);
        }

        if ($flags & SSH_FILEXFER_ATTR_ACMODTIME) {
            $r = unpack('Natime/Nmtime', $attrs);
            $attrs = substr($attrs, 8);
            $ret = array_merge($ret, $r);
        }

        // XXX: 還有 SSH_FILEXFER_ATTR_EXTENDED
        return $ret;
    }

    public function getattrs($path, $options = array())
    {
        $full = array_key_exists('full', $options) ? intval($options['full']) : 0;
        $absolute_path = array_key_exists('absolute_path', $options) ? intval($options['absolute_path']) : 0;

        list($project, $path) = $this->parsePath($path);
        if (!file_exists($this->getRealPath($project, $path))) {
            throw new Exception("file not found", 404);
        }

        if ($full) {
            $flag = SSH_FILEXFER_ATTR_SIZE | SSH_FILEXFER_ATTR_UIDGID | SSH_FILEXFER_ATTR_PERMISSIONS | SSH_FILEXFER_ATTR_ACMODTIME;
        } else {
            $flag = 0;
        }
        $data = '';
        $data .= pack('N', $flag);
        if ($flag & SSH_FILEXFER_ATTR_SIZE) {
            if ($project) {
                $size = $this->getFileSize($project, $path);
            } else {
                $size = 0;
            }
            $data .= pack('NN', $size / 0x100000000, $size);
        }

        if ($flag & SSH_FILEXFER_ATTR_UIDGID) {
            // 這邊沒有 uid, gid 的概念
            $uid = 1000;
            $gid = 1000;
            $data .= pack('NN', $uid, $gid);
        }

        if ($flag & SSH_FILEXFER_ATTR_PERMISSIONS) {
            if ($project) {
                $permission = $this->getFilePermission($project, $path);
            } else {
                $permission = 0x4700;
            }
            $data .= pack('N', $permission);
        }

        if ($flag & SSH_FILEXFER_ATTR_ACMODTIME) {
            if ($project) {
                list($atime, $mtime) = $this->getFileTime($project, $path);
            } else {
                $atime = $mtime = strtotime('2013/1/1');
            }
            $data .= pack('NN', $atime, $mtime);
        }

        if ($flag & SSH_FILEXFER_ATTR_EXTENDED) {
            // 用不到吧...
            $data .= pack('N', 0);
        }

        return $data;
    }

    public function getAccount()
    {
        return substr($this->user->name, 0, 8);
    }

    public function return_name_info($request_id, $base, $filenames, $options = array())
    {
        $full = array_key_exists('full', $options) ? intval($options['full']) : 0;
        $absolute_path = array_key_exists('absolute_path', $options) ? intval($options['absolute_path']) : 0;

        $data = pack('NN', $request_id, count($filenames));

        foreach ($filenames as $filename) {
            $path = $this->getFTPAbsolutePath($base, $filename);
            if ($path == '/') {
                if ($absolute_path) {
                    $filename = '/';
                }
                $longname = sprintf("%10s %3d %8s %8s %-8d %12s %s", 'drwx------', 1, $this->getAccount(), $this->getAccount(), 4096, 'Feb 31 00:00', $filename);
            } else {
                if ($absolute_path) {
                    $filename = $path;
                }
                list($project, $project_path) = $this->parsePath($path);
                list($atime, $mtime) = $this->getFileTime($project, $project_path);
                $longname = sprintf("%10s %3d %8s %8s %-8d %12s %s", $this->getPermWord($this->getFilePermission($project, $project_path)),
                    1,
                    $this->getAccount(),
                    $this->getAccount(),
                    $this->getFileSize($project, $project_path),
                    date('M j H:i', $mtime),
                    $filename
                );
            }
            $data .= pack('Na*Na*a*',
                strlen($filename),
                $filename,
                strlen($longname),
                $longname,
                $this->getattrs($path, $options)
            );
        }
        $this->send(SSH_FXP_NAME, $data);
    }

    protected $_projects = null;
    public function getProjectsByUser()
    {
        if (is_null($this->_projects)) {
            $project_ids = $this->user->project_members->toArray('project_id');
            $projects = array();
            foreach (Project::search(1)->searchIn('id', $project_ids)->toArray('name') as $name) {
                if (file_exists($this->getRealPath($name, '/'))) {
                    $projects[] = $name;
                }
            }
            $this->_projects = $projects;
        }

        return $this->_projects;
    }

    public function openFile($request_id, $filename, $pflags, $attrs)
    {
        list($project, $project_path) = $this->parsePath($filename);
        $handle = $this->_handle_serial ++;
        $infos = array();
        $infos['path'] = $filename;

        if (!$project) {
            $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_NO_SUCH_FILE));
            return;
        } 

        $flag = '';
        if ($pflags & SSH_FXF_CREAT) {
            $flag .= 'c';
        } elseif ($pflags & SSH_FXF_APPEND) {
            $flag .= 'a';
        } elseif ($pflags & SSH_FXF_WRITE) {
            $flag .= 'w';
        }

        $path = $this->getRealPath($project, $project_path);

        if ($flag != '') {
            $attrs = $this->parseAttrs($attrs);
            if ($attrs['mtime'] and $attrs['atime']) {
                touch($path, $attrs['mtime'], $attrs['atime']);
            }
            if ($attrs['permissions']) {
                chmod($path, $attrs['permissions']);
            }
        }

        if ($pflags & SSH_FXF_READ) {
            if ($flag == '') {
                $flag .= 'r';
            } else {
                $flag .= '+';
            }
        }
        $flag .= 'b';


        $infos['fp'] = fopen($path, $flag);
        if (!$infos['fp']) {
            $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_FAILURE));
            return;
        }
        $infos['filesize'] = filesize($path);
        $this->_handle_infos[$handle] = $infos;
        $this->send(SSH_FXP_HANDLE, pack('NN', $request_id, strlen($handle)) . $handle);
    }

    public function main($user)
    {
        $this->fp = fopen('php://stdin', 'r');
        $this->output = fopen('php://stdout', 'w');
        $this->path = '/';

        if (!$user = User::find_by_name($user)) {
            return;
        }
        $this->user = $user;

        while (true) {
            $ret = $this->read();
            if (false === $ret) {
                break;
            }
            list($type, $data) = $ret;

            switch ($type) {
            case SSH_FXP_INIT:
                $this->send(SSH_FXP_VERSION, pack('N', 3));
                break;

            case SSH_FXP_REALPATH:
                $ret = unpack('Nid/Npath_length/a*path', $data);
                $path = $this->getFTPAbsolutePath($this->path, $ret['path']);

                try {
                    $this->return_name_info($ret['id'], $path, array(''), array('absolute_path' => true));
                    $this->path = $path;
                } catch (Exception $e) {
                    if ($e->getCode() == 404) {
                        $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_NO_SUCH_FILE));
                    } else {
                        throw $e;
                    }
                }
                break;

            case SSH_FXP_OPENDIR:
                $ret = unpack('Nid/Npath_length/a*path', $data);
                $path = $this->getFTPAbsolutePath($this->path, $ret['path']);
                list($project, $project_path) = $this->parsePath($path);
                $handle = $this->_handle_serial ++;
                $infos = array();
                $infos['path'] = $path;

                if (!$project) {
                    $infos['files'] = $this->getProjectsByUser();
                } else {
                    $infos['dir'] = opendir($this->getRealPath($project, $project_path));
                }
                $this->_handle_infos[$handle] = $infos;
                $this->send(SSH_FXP_HANDLE, pack('NN', $ret['id'], strlen($handle)), $handle);
                break;

            case SSH_FXP_READDIR:
                $ret = unpack('Nid/Nhandle_length/a*handle', $data);
                $handle = $ret['handle'];

                if (!array_key_exists($handle, $this->_handle_infos)) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                    break;
                }

                if (array_key_exists('files', $this->_handle_infos[$handle])) {
                    if (0 == count($this->_handle_infos[$handle]['files'])) {
                        $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_EOF));
                        break;
                    }
                    $filenames = array();
                    for ($i = 0; $i < 100 and count($this->_handle_infos[$handle]['files']); $i ++) {
                        $filenames[] = array_pop($this->_handle_infos[$handle]['files']);
                    }
                    $this->return_name_info($ret['id'], $this->_handle_infos[$handle]['path'], $filenames, array('full' => true));
                } elseif (array_key_exists('dir', $this->_handle_infos[$handle])) {
                    $filenames = array();
                    while ($dir = readdir($this->_handle_infos[$handle]['dir'])) {
                        $filenames[] = $dir;
                        if (count($filenames) >= 100) {
                            break;
                        }
                    }

                    if (!$filenames) {
                        $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_EOF));
                        break;
                    }
                    $this->return_name_info($ret['id'], $this->_handle_infos[$handle]['path'], $filenames, array('full' => true));
                } else {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                    break;
                }

                break;

            case SSH_FXP_CLOSE:
                $ret = unpack('Nid/Nhandle_length/a*handle', $data);
                $handle = $ret['handle'];

                if (!array_key_exists($handle, $this->_handle_infos)) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                    break;
                }

                if (array_key_exists('dir', $this->_handle_infos[$handle])) {
                    closedir($this->_handle_infos[$handle]['dir']);
                } elseif (array_key_exists('fp', $this->_handle_infos[$handle])) {
                    fclose($this->_handle_infos[$handle]['fp']);
                }
                unset($this->_handle_infos[$handle]);
                $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_OK));
                break;

            case SSH_FXP_STAT:
            case SSH_FXP_LSTAT:
                $ret = unpack('Nid/Npath_length/a*path', $data);
                try {
                    $this->send(SSH_FXP_ATTRS, pack('N', $ret['id']) . $this->getattrs($ret['path'], array('full' => true)));
                } catch (Exception $e) {
                    if ($e->getCode() == 404) {
                        $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_NO_SUCH_FILE));
                    } else {
                        throw $e;
                    }
                }
                break;

            case SSH_FXP_MKDIR:
                $ret = unpack('Nid/Npath_length', $data);
                $path = substr($data, 8, $ret['path_length']);
                $path = $this->getFTPAbsolutePath($this->path, $path);
                list($project, $project_path) = $this->parsePath($path);
                $real_path = $this->getRealPath($project, $project_path);

                $this->logger($real_path);
                if (mkdir($real_path)) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_OK));
                } else {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                }
                break;

            case SSH_FXP_RMDIR:
                $ret = unpack('Nid/Npath_length', $data);
                $path = substr($data, 8, $ret['path_length']);
                $path = $this->getFTPAbsolutePath($this->path, $path);
                list($project, $project_path) = $this->parsePath($path);
                $real_path = $this->getRealPath($project, $project_path);

                if (rmdir($real_path)) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_OK));
                } else {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                }
                break;

            case SSH_FXP_OPEN:
                $ret = unpack('Nid/Nfilename_length', $data);
                $request_id = $ret['id'];
                $filename_length = $ret['filename_length'];
                $filename = substr($data, 8, $filename_length);
                $ret = unpack('Npflags/a*attrs', substr($data, 8 + $filename_length));

                $this->openFile($request_id, $filename, $ret['pflags'], $ret['attrs']);
                break;

            case SSH_FXP_WRITE:
                $ret = unpack('Nid/Nhandle_length', $data);
                $request_id = $ret['id'];
                $handle_length = $ret['handle_length'];
                $handle = substr($data, 8, $handle_length);
                $ret = unpack('Noffset_upper/Noffset_lower/Ndata_length', substr($data, 8 + $handle_length));
                $offset = $ret['offset_upper'] * 0x100000000 + $ret['offset_lower'];
                $data = substr($data, 8 + $handle_length + 12, $ret['data_length']);

                if (!array_key_exists($handle, $this->_handle_infos) or !array_key_exists('fp', $this->_handle_infos[$handle])) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                    break;
                }
                $fp = $this->_handle_infos[$handle]['fp'];
                // TODO: 檢查不合法的 offset, len
                if (fseek($fp, $offset) < 0) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_FAILURE));
                    break;
                }
                fwrite($fp, $data);
                $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_OK));
                break;

            case SSH_FXP_READ:
                $ret = unpack('Nid/Nhandle_length', $data);
                $request_id = $ret['id'];
                $handle_length = $ret['handle_length'];
                $handle = substr($data, 8, $handle_length);
                $ret = unpack('Noffset_upper/Noffset_lower/Nlen', substr($data, 8 + $handle_length));
                $offset = $ret['offset_upper'] * 0x100000000 + $ret['offset_lower'];
                $len = $ret['len'];

                if (!array_key_exists($handle, $this->_handle_infos) or !array_key_exists('fp', $this->_handle_infos[$handle])) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                    break;
                }
                $fp = $this->_handle_infos[$handle]['fp'];
                $filesize = $this->_handle_infos[$handle]['filesize'];

                // TODO: 檢查不合法的 offset, len
                if (fseek($fp, $offset) < 0) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_FAILURE));
                    break;
                }

                $data = fread($fp, $len);
                if (false === $data) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_FAILURE));
                } else if (strlen($data) == 0) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_EOF));
                } else {
                    $this->send(SSH_FXP_DATA, pack('NN', $request_id, strlen($data)), $data);
                }
                break;

            case SSH_FXP_FSETSTAT:
            case SSH_FXP_SETSTAT:
                $ret = unpack('Nid/Npathhandle_length', $data);
                $request_id = $ret['id'];
                $pathhandle = substr($data, 8, $ret['pathhandle_length']);
                $attrs = substr($data, 8 + $ret['pathhandle_length']);

                if (SSH_FXP_FSETSTAT == $type) {
                    $handle = $pathhandle;
                    if (!array_key_exists($handle, $this->_handle_infos) or !array_key_exists('fp', $this->_handle_infos[$handle])) {
                        $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_FAILURE));
                        break;
                    }
                    $path = $this->_handle_infos[$handle]['path'];
                } else {
                    $path = $pathhandle;
                }

                $ftp_path = $this->getFTPAbsolutePath($this->path, $path);
                list($project, $project_path) = $this->parsePath($ftp_path);
                $path = $this->getRealPath($project, $project_path);
                if (!file_exists($path)) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_NO_SUCH_FILE));
                    break;
                }
                $attrs = $this->parseAttrs($attrs);
                if ($attrs['mtime'] and $attrs['atime']) {
                    touch($path, $attrs['mtime'], $attrs['atime']);
                }
                if ($attrs['permissions']) {
                    chmod($path, $attrs['permissions']);
                }
                $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_OK));
                break;

            case SSH_FXP_REMOVE:
                $ret = unpack('Nid/Nfilename_length', $data);
                $request_id = $ret['id'];
                $filename = substr($data, 8, $ret['filename_length']);

                $path = $this->getFTPAbsolutePath($this->path, $filename);
                list($project, $project_path) = $this->parsePath($path);
                $real_path = $this->getRealPath($project, $project_path);

                if (unlink($real_path)) {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_OK));
                } else {
                    $this->send(SSH_FXP_STATUS, pack('NN', $request_id, SSH_FX_FAILURE));
                }
                break;

            default:
                $ret = unpack('Nid', $data);
                $this->send(SSH_FXP_STATUS, pack('NN', $ret['id'], SSH_FX_OP_UNSUPPORTED));
                return;
            }
        }
    }
}
