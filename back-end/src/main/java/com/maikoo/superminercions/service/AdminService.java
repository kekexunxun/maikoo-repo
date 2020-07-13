package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.LoginDTO;

public interface AdminService {
    LoginDTO login(String username, String password);

    void updatePassword(String phone, String password);
}
