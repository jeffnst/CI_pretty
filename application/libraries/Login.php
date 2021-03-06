<?php  defined('BASEPATH') or exit('No direct script access allowed');

class Login
{
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->helper(['file']);
    }

    public function render()
    {
        $setting = json_decode(read_file(SETTING));

        foreach ($this->CI->_config['field'] as $k=>$f) {
            $field[$k] = $this->CI->security->xss_clean($this->CI->input->post($f));
        }

        if (filter_var($field['identity'], FILTER_VALIDATE_EMAIL)) {
            $where['email'] = $field['identity'];
            $identity = 'email';
        } else {
            $where['username'] = $field['identity'];
            $identity = 'username';
        }

        $this->CI->db
            ->select($this->CI->_config['select'])
            ->from($this->CI->_config['table']);

        foreach ($this->CI->_config['join'] as $j) {
            $this->CI->db->join($j[0], $j[1], $j[2]);
        }

        $data = $this->CI->db
            ->where($where)
            ->limit(1)
            ->get()
            ->row();

				if ($data==null || $data->id==null) {
            $this->CI->response->success(['status'=>'unregister']);
            return false;
        }

        if (password_verify($field['password'], $data->password)) {
            $user = [
                'user_id' => $data->id,
                'roles' => array_unique(explode('|', $data->roles)),
                'jobs' => array_unique(explode('|', $data->jobs)),
                'leader' => $data->leader,
                'parentClassroom' => $data->parentClassroom,
                'identityValue' => $field['identity'],
                'identity' => $identity,
                'year' => $setting->year,
                'semester' => $setting->semester,
                'login' => true,
            ];

						$this->CI->response->success(['status'=>'login'], $user);
            return false;
        }

        $this->CI->response->success(['status'=>'pass_mismatch']);
    }
}
