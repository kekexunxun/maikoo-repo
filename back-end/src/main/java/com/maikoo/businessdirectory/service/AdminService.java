package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.TokenDTO;

public interface AdminService {
    TokenDTO login(String username, String password);

    void updatePassword(String oldPassword, String newPassword);
}
