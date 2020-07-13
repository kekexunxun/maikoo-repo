package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.SMCOrderDO;
import lombok.Data;

import java.math.BigDecimal;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class SMCTradingWithCustomerDTO {
    private String userName;
    private String userMobile;
    private BigDecimal handlingFee;
    private BigDecimal listFee;
    @JsonIgnoreProperties({"list_price"})
    @JsonUnwrapped
    private SMCTradingInformationDTO smcTradingInformationDTO;

    public static SMCTradingWithCustomerDTO valueOf(SMCOrderDO smcOrderDO) {
        SMCTradingWithCustomerDTO smcTradingWithCustomerDTO = new SMCTradingWithCustomerDTO();
        smcTradingWithCustomerDTO.setUserName(smcOrderDO.getCustomerDO().getName());
        smcTradingWithCustomerDTO.setUserMobile(smcOrderDO.getCustomerDO().getPhone());
        smcTradingWithCustomerDTO.setSmcTradingInformationDTO(SMCTradingInformationDTO.valueOf(smcOrderDO));
        smcTradingWithCustomerDTO.setHandlingFee(smcOrderDO.getFee());
        smcTradingWithCustomerDTO.setListFee(smcTradingWithCustomerDTO.getSmcTradingInformationDTO().getListPrice());
        if (smcTradingWithCustomerDTO.getSmcTradingInformationDTO().getRemark() == null) {
            smcTradingWithCustomerDTO.getSmcTradingInformationDTO().setRemark(smcOrderDO.getNote() == null ? "" : smcOrderDO.getNote());
        }
        if (smcTradingWithCustomerDTO.getSmcTradingInformationDTO().getFinishAt() == null) {
            smcTradingWithCustomerDTO.getSmcTradingInformationDTO().setFinishAt("");
        }
        return smcTradingWithCustomerDTO;
    }
}
