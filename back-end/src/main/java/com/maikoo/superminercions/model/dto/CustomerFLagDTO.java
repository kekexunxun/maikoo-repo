package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonProperty;
import com.maikoo.superminercions.model.CustomerDO;

public class CustomerFLagDTO {
    private boolean isAuth;
    private boolean isSetPass;
    private boolean isSetTransPwd;
    private boolean isBindAlipay;
    private boolean isBindCard;

    @JsonProperty(value="is_auth")
    public boolean isAuth() {
        return isAuth;
    }

    @JsonProperty(value="is_set_pass")
    public boolean isSetPass() {
        return isSetPass;
    }

    @JsonProperty(value="is_set_transPwd")
    public boolean isSetTransPwd() {
        return isSetTransPwd;
    }

    @JsonProperty(value="is_bind_alipay")
    public boolean isBindAlipay() {
        return isBindAlipay;
    }

    @JsonProperty(value="is_bind_card")
    public boolean isBindCard() {
        return isBindCard;
    }

    public void setAuth(boolean auth) {
        isAuth = auth;
    }

    public void setSetPass(boolean setPass) {
        isSetPass = setPass;
    }

    public void setSetTransPwd(boolean setTransPwd) {
        isSetTransPwd = setTransPwd;
    }

    public void setBindAlipay(boolean bindAlipay) {
        isBindAlipay = bindAlipay;
    }

    public void setBindCard(boolean bindCard) {
        isBindCard = bindCard;
    }

    public static CustomerFLagDTO valueOf(CustomerDO customerDO){
        CustomerFLagDTO customerFLagDTO = new CustomerFLagDTO();
        customerFLagDTO.setAuth(customerDO.isUpdatedAuthentication());
        customerFLagDTO.setSetPass(customerDO.isUpdatedPassword());
        customerFLagDTO.setSetTransPwd(customerDO.isUpdatedTransactionPassword());
        customerFLagDTO.setBindAlipay(customerDO.isBindAli());
        customerFLagDTO.setBindCard(customerDO.isBindBank());
        return customerFLagDTO;
    }
}
