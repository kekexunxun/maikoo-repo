package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.PhoneDTO;
import com.maikoo.businessdirectory.model.dto.TokenDTO;
import com.maikoo.businessdirectory.model.dto.UserDTO;
import com.maikoo.businessdirectory.model.query.PhoneDecryptQuery;
import com.maikoo.businessdirectory.model.query.UserQuery;

import java.util.List;

public interface UserService {
    TokenDTO login(String code);

    void updateAuthentication(UserQuery userQuery);

    PhoneDTO phoneDecrypt(PhoneDecryptQuery phoneDecryptQuery);

    UserDTO information();

    List<UserDTO> getUserList();
}
