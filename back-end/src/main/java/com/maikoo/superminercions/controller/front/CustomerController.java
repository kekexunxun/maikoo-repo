package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.*;
import com.maikoo.superminercions.service.CustomerService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/customer")
public class CustomerController {
    @Autowired
    private CustomerService customerService;

    @RequestMapping("/balance")
    public ResponseDTO<CustomerBalanceDTO> balance(@RequestParam(name = "assetType", defaultValue = "ALL", required = false) String type, @RequestParam(name = "assetStatus",  defaultValue = "ALL", required = false) String status){
        return new ResponseDTO(200, "获取成功", customerService.balance(type, status));
    }

    @RequestMapping("/wallet")
    public ResponseDTO<WalletDTO> walletInformation(){
        return new ResponseDTO<>(200, "获取成功", customerService.walletInformation());
    }

    @RequestMapping("/wallet/update")
    public ResponseDTO updateWallet(@RequestParam("walletAddress") String wallet){
        customerService.updateWallet(wallet);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/product")
    public ResponseDTO<List<CustomerProductDTO>> productList(){
        return new ResponseDTO<>(200, "获取成功", customerService.productList());
    }

    @RequestMapping("/product/information")
    public ResponseDTO<CustomerProductInformationDTO> productInformation(@RequestParam("minerSn") String userProductSN){
        return new ResponseDTO<>(200, "获取成功", customerService.productInformation(userProductSN));
    }

    @RequestMapping("/flag")
    public ResponseDTO<CustomerFLagDTO> flag(){
        return new ResponseDTO<>(200, "获取成功", customerService.flag());
    }

    @RequestMapping("/authentication")
    public ResponseDTO authentication(@Validated CustomerAuthenticationQuery customerAuthenticationQuery){
        customerService.authentication(customerAuthenticationQuery);
        return new ResponseDTO(200, "认证成功", null);
    }

    @RequestMapping("/authentication/information")
    public ResponseDTO<AuthenticationInformationDTO> authenticationInformation(){
        return new ResponseDTO(200, "获取成功", customerService.authenticationInformation());
    }

    @RequestMapping("/ali")
    public ResponseDTO ali(@Validated CustomerAliQuery customerAliQuery){
        customerService.ali(customerAliQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/ali/information")
    public ResponseDTO<AliInformationDTO> aliInformation(){
        return new ResponseDTO(200, "获取成功", customerService.aliInformation());
    }

    @RequestMapping("/bank")
    public ResponseDTO bank(@Validated CustomerBankQuery customerBankQuery){
        customerService.bank(customerBankQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/bank/information")
    public ResponseDTO<BankInformationDTO> bankInformation(){
        return new ResponseDTO(200, "获取成功", customerService.bankInformation());
    }

    @RequestMapping("/trading-password")
    public ResponseDTO tradingPassword(@Validated CustomerTradingPasswordQuery customerTradingPasswordQuery){
        customerService.tradingPassword(customerTradingPasswordQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/password")
    public ResponseDTO password(@Validated CustomerPasswordQuery customerPasswordQuery){
        customerService.password(customerPasswordQuery);
        return new ResponseDTO(200, "修改成功", null);
    }
}
