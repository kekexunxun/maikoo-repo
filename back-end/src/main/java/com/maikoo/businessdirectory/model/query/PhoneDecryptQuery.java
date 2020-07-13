package com.maikoo.businessdirectory.model.query;

import lombok.Data;

@Data
public class PhoneDecryptQuery {
    private String encryptedData;
    private String iv;
    private String code;
}
