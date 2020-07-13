package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.*;

import java.util.List;

public interface CustomerService {
    CustomerFLagDTO flag();

    LoginDTO login(String username, String password);

    CustomerBalanceDTO balance(String type, String status);

    WalletDTO walletInformation();

    void updateWallet(String wallet);

    void resetPassword(String password, String phone);

    List<CustomerProductDTO> productList();

    CustomerProductInformationDTO productInformation(String userProductSN);

    void authentication(CustomerAuthenticationQuery customerAuthenticationQuery);

    void ali(CustomerAliQuery customerAliQuery);

    void bank(CustomerBankQuery customerBankQuery);

    void tradingPassword(CustomerTradingPasswordQuery customerTradingPasswordQuery);

    void password(CustomerPasswordQuery customerPasswordQuery);

    ExchangeRateDTO exchangeRate();

    void sendCaptcha(String phone);

    void checkCaptcha(String phone, String captcha);

    AuthenticationInformationDTO authenticationInformation();

    AliInformationDTO aliInformation();

    BankInformationDTO bankInformation();

    List<CustomerDTO> list();

    void add(CustomerQuery customerQuery);

    void update(CustomerQuery customerQuery);

    void updateStatus(long id, String status);

    List<CustomerBalanceWithCustomerDTO> balanceListAll();

    List<CustomerBaseDTO> baseListAll();

    CustomerBaseInformationDTO baseInformation(long id);

    List<CustomerProductWithCustomerDTO> productListAll();

    void updateProductStatus(long customerProductId, String status);

    void updateProduct(CustomerProductQuery customerProductQuery);

    List<CustomerProductApplyDTO> productApplyListAll();

    void updateProductApplyStatus(OrderQuery orderQuery);

    void updateProductApplyNote(OrderQuery orderQuery);
}
