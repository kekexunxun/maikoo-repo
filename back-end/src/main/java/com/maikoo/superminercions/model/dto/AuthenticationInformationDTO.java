package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerDO;
import lombok.Data;

@Data
public class AuthenticationInformationDTO {
    private String userAccount;
    private String userName;
    private String userMobile;
    private String userIdentId;
    private String identAboveUrl;
    private String identBelowUrl;

    public static AuthenticationInformationDTO valueOf(CustomerDO customerDO) {
        AuthenticationInformationDTO authenticationInformationDTO = new AuthenticationInformationDTO();
        authenticationInformationDTO.setUserAccount(customerDO.getUsername());
        authenticationInformationDTO.setUserName(customerDO.getName());
        authenticationInformationDTO.setUserMobile(customerDO.getPhone());
        authenticationInformationDTO.setUserIdentId(customerDO.getIdCard());
        authenticationInformationDTO.setIdentAboveUrl(customerDO.getFrontIdCardUri());
        authenticationInformationDTO.setIdentBelowUrl(customerDO.getBackIdCardUri());
        return authenticationInformationDTO;
    }
}
