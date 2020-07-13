package com.maikoo.superminercions.service.impl;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.github.qcloudsms.SmsSingleSender;
import com.github.qcloudsms.SmsSingleSenderResult;
import com.github.qcloudsms.httpclient.HTTPException;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.*;
import com.maikoo.superminercions.exception.*;
import com.maikoo.superminercions.model.*;
import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.*;
import com.maikoo.superminercions.service.CustomerService;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.EncryptUtil;
import org.apache.commons.lang3.RandomStringUtils;
import org.apache.commons.lang3.RandomUtils;
import org.json.JSONException;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.dao.DuplicateKeyException;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.CollectionUtils;
import org.springframework.util.StringUtils;

import javax.annotation.Resource;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.TimeUnit;

@Service
public class CustomerServiceImpl implements CustomerService {
    @Resource
    private RedisTemplate<String, Object> redisTemplate;
    @Autowired
    private CustomerDao customerDao;
    @Autowired
    private CustomerProductDao customerProductDao;
    @Autowired
    private CustomerProductApplyDao customerProductApplyDao;
    @Autowired
    private OrderDao orderDao;
    @Autowired
    private SettingDao settingDao;
    @Autowired
    private MessageRecordDao messageRecordDao;
    @Autowired
    private ObjectMapper objectMapper;
    @Autowired
    private HttpSession session;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Override
    public CustomerFLagDTO flag() {
        CustomerFLagDTO customerFLagDTO = null;
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        CustomerDO customerFlagDO = customerDao.selectFlag(currentCustomerDO.getId());
        if (customerFlagDO != null) {
            customerFLagDTO = CustomerFLagDTO.valueOf(customerFlagDO);
        } else {
            throw new InvalidParameterException("无效的用户");
        }
        return customerFLagDTO;
    }

    @Override
    public LoginDTO login(String username, String password) {
        CustomerDO customerDO = customerDao.login(username, EncryptUtil.password(password));

        if (customerDO == null) {
            throw new InvalidUsernameOrPasswordException("用户名或密码错误");
        }

        if(customerDO.isDisable()){
            throw new CustomerDisabledException();
        }

        LoginDTO loginDTO = new LoginDTO();
        loginDTO.setAccessToken(EncryptUtil.token(username + System.currentTimeMillis()));
        try {
            AccountDO<CustomerDO> customerAccountDO = new AccountDO<>();
            customerAccountDO.setAccountType(ConstantUtil.FRONT_ACCOUNT_TYPE);
            customerAccountDO.setUserDO(customerDO);
            redisTemplate.opsForValue().set(loginDTO.getAccessToken(), objectMapper.writeValueAsString(customerAccountDO));
            redisTemplate.expire(loginDTO.getAccessToken(), 30, TimeUnit.DAYS);
        } catch (JsonProcessingException e) {
            throw new RuntimeException(e);
        }
        return loginDTO;
    }

    @Override
    public CustomerBalanceDTO balance(String type, String status) {
        CustomerBalanceDTO customerBalanceDTO = new CustomerBalanceDTO();

        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        CustomerDO customerDO = customerDao.selectBalance(currentCustomerDO.getId());
        BalanceDTO smc = null;
        BalanceDTO eth = null;
        if (("ALL").equals(type) || ("SMC").equals(type)) {
            smc = new BalanceDTO();
            smc.setTotal(customerDO.getSmcBalance());
            smc.setAvailable(customerDO.getAvailableSMCBalance());
        }
        if (("ALL").equals(type) || ("ETH").equals(type)) {
            eth = new BalanceDTO();
            eth.setTotal(customerDO.getEthBalance());
            eth.setAvailable(customerDO.getAvailableETHBalance());
        }

        if (smc != null) {
            removeBalance(smc, status);
            customerBalanceDTO.setSmc(smc);
        }
        if (eth != null) {
            removeBalance(eth, status);
            customerBalanceDTO.setEth(eth);
        }

        return customerBalanceDTO;
    }

    @Override
    public WalletDTO walletInformation() {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        String wallet = customerDao.walletInformation(currentCustomerDO.getId());
        WalletDTO walletDTO = new WalletDTO();
        walletDTO.setWalletAddress(wallet);
        return walletDTO;
    }

    @Override
    public void updateWallet(String wallet) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        if(customerDao.updateWallet(currentCustomerDO.getId(), wallet) == 0){
            throw new UpdateCustomerInformationException();
        }
    }

    @Override
    public void resetPassword(String password, String phone) {
        if (customerDao.resetPassword(EncryptUtil.password(password), phone) == 0) {
            throw new ResetPasswordException();
        }
    }

    @Override
    public List<CustomerProductDTO> productList() {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        List<CustomerProductDTO> customerProductDTOList = new ArrayList<>();
        List<CustomerProductDO> customerProductDOList  = null;
        try {
            customerProductDOList = customerProductDao.selectByUser(currentCustomerDO.getId());
            if (customerProductDOList != null && customerProductDOList.size() > 0) {
                customerProductDOList.forEach(customerProductDO -> customerProductDTOList.add(CustomerProductDTO.valueOf(customerProductDO)));
            }
        } catch (Exception e) {
            throw new GetMinerListException("矿机列表获取失败");
        }

        return customerProductDTOList;
    }

    @Override
    public CustomerProductInformationDTO productInformation(String userProductSN) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        CustomerProductInformationDTO customerProductInformationDTO = null;

        try {
            CustomerProductDO customerProductDO = customerProductDao.selectOneByUserProductSN(userProductSN);
            if (customerProductDO != null && customerProductDO.getCustomerDO() != null && customerProductDO.getCustomerDO().getId().equals(currentCustomerDO.getId())) {
                customerProductInformationDTO = CustomerProductInformationDTO.valueOf(customerProductDO);
            }
        } catch (Exception e) {
            throw new InvalidParameterException("无效的产品编号");
        }
        return customerProductInformationDTO;
    }

    @Override
    public void authentication(CustomerAuthenticationQuery customerAuthenticationQuery) {
        String[] images = customerAuthenticationQuery.getIdentImg().split(":");
        if (images.length != 2) {
            throw new InvalidParameterException("无效的身份证照片地址");
        }

        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(currentCustomerDO.getId());
        customerDO.setAccount(customerAuthenticationQuery.getMemAccount());
        customerDO.setName(customerAuthenticationQuery.getMemName());
        customerDO.setPhone(customerAuthenticationQuery.getMobile());
        customerDO.setIdCard(customerAuthenticationQuery.getIdentID());
        customerDO.setFrontIdCardUri(images[0]);
        customerDO.setBackIdCardUri(images[1]);

        if(customerDao.updateAuthentication(customerDO) == 0){
            throw new UpdateCustomerInformationException();
        }
    }

    @Override
    public void ali(CustomerAliQuery customerAliQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        //TODO: 验证码检验

        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(currentCustomerDO.getId());
        customerDO.setAli(customerAliQuery.getAlipayAccount());

        if(customerDao.updateAli(customerDO) == 0){
            throw new UpdateCustomerInformationException();
        }
    }

    @Override
    public void bank(CustomerBankQuery customerBankQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        //TODO: 验证码检验

        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(currentCustomerDO.getId());
        customerDO.setBank(customerBankQuery.getBankName());
        customerDO.setBankBranch(customerBankQuery.getBankBranch());
        customerDO.setBankCardNumber(customerBankQuery.getBankCard());

        if(customerDao.updateBank(customerDO) == 0){
            throw new UpdateCustomerInformationException();
        }
    }

    @Override
    public void tradingPassword(CustomerTradingPasswordQuery customerTradingPasswordQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        String newPassword = EncryptUtil.password(customerTradingPasswordQuery.getPassword());

        if (customerDao.checkOldTradingPassword(currentCustomerDO.getId(), newPassword)) {
            throw new SamePasswordException();
        }

        //TODO: 验证码检验

        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(currentCustomerDO.getId());
        customerDO.setTradingPasswordPhone(customerTradingPasswordQuery.getMobile());
        customerDO.setTradingPassword(newPassword);

        if(customerDao.updateTradingPassword(customerDO) == 0){
            throw new UpdatePasswordException();
        }
    }

    @Override
    public void password(CustomerPasswordQuery customerPasswordQuery) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        String newPassword = EncryptUtil.password(customerPasswordQuery.getPassword());

        if (customerDao.checkOldPassword(currentCustomerDO.getId(), newPassword)) {
            throw new SamePasswordException();
        }

        //TODO: 验证码检验

        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(currentCustomerDO.getId());
        customerDO.setPasswordPhone(customerPasswordQuery.getMobile());
        customerDO.setPassword(newPassword);

        if (customerDao.updatePassword(customerDO) == 0) {
            throw new UpdatePasswordException();
        }
    }

    @Override
    public ExchangeRateDTO exchangeRate() {
        ExchangeRateDTO exchangeRateDTO;
        try {
            SettingDO settingDO = settingDao.select();
            exchangeRateDTO = new ExchangeRateDTO();
            exchangeRateDTO.setEth2rmb(settingDO.getEthPrice());
            exchangeRateDTO.setSmc2rmb(settingDO.getSmcPrice());
        }catch (Exception e){
            throw new ExchangeRateException();
        }

        return exchangeRateDTO;
    }

    @Override
    public void sendCaptcha(String phone) {
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");

        if(!currentCustomerDO.getPhone().equals(phone)){
            throw new DiscordPhoneException();
        }

        String[] params = {RandomUtils.nextInt(1000, 10000) + "", "10"};
        try {
            SmsSingleSender sender = new SmsSingleSender(customEnvironmentConfig.getMessageAppId(), customEnvironmentConfig.getMessageAppKey());
            SmsSingleSenderResult result = sender.sendWithParam("86", phone,
                    customEnvironmentConfig.getMessageTemplateId(), params, customEnvironmentConfig.getMessageSign(), "", "");
            if (result.result != 0) {
                throw new RuntimeException(result.errMsg);
            }
        } catch (HTTPException e) {
            throw new RuntimeException(e);
        } catch (JSONException e) {
            throw new RuntimeException(e);
        } catch (IOException e) {
            throw new RuntimeException(e);
        }

        MessageRecordDO messageRecordDO = new MessageRecordDO();
        messageRecordDO.setCustomerDO(currentCustomerDO);
        messageRecordDO.setPhone(phone);
        messageRecordDao.insert(messageRecordDO);

        String key = phone + "captcha";
        CaptchaDO captchaDO = new CaptchaDO();
        captchaDO.setCaptcha(params[0]);
        captchaDO.setUsed(false);
        redisTemplate.opsForValue().set(key, captchaDO);
        redisTemplate.expire(key, 1, TimeUnit.HOURS);
    }

    @Override
    public void checkCaptcha(String phone, String captcha) {
        String key = phone + "captcha";

        if (!redisTemplate.hasKey(key)) {
            throw new InvalidParameterException();
        }

        Long expire = redisTemplate.getExpire(key);

        if (expire <= (60 * 60 - 10 * 60)) {
            throw new CaptchaTimeOutException("验证码已过期");
        }

        CaptchaDO captchaDO = (CaptchaDO) redisTemplate.opsForValue().get(key);

        if(captchaDO.isUsed()){
            throw new UsedCaptchaException();
        }

        if (captchaDO.getCaptcha().equals(captcha)) {
            captchaDO.setUsed(true);
            redisTemplate.opsForValue().set(key, captchaDO, expire, TimeUnit.SECONDS);
        }else{
            throw new InvalidCaptchaException();
        }
    }

    @Override
    public AuthenticationInformationDTO authenticationInformation() {
        AuthenticationInformationDTO authenticationInformationDTO;
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        CustomerDO customerDO = customerDao.selectAuthentication(currentCustomerDO.getId());
        if (customerDO != null) {
            customerDO.setId(currentCustomerDO.getId());
            authenticationInformationDTO = AuthenticationInformationDTO.valueOf(customerDO);
        } else {
            authenticationInformationDTO = new AuthenticationInformationDTO();
        }
        return authenticationInformationDTO;
    }

    @Override
    public AliInformationDTO aliInformation() {
        AliInformationDTO aliInformationDTO;
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        CustomerDO customerDO = customerDao.selectAli(currentCustomerDO.getId());
        if (customerDO != null) {
            customerDO.setId(currentCustomerDO.getId());
            aliInformationDTO = AliInformationDTO.valueOf(customerDO);
        } else {
            aliInformationDTO = new AliInformationDTO();
        }
        return aliInformationDTO;
    }

    @Override
    public BankInformationDTO bankInformation() {
        BankInformationDTO bankInformationDTO;
        CustomerDO currentCustomerDO = (CustomerDO) session.getAttribute("current_customer");
        CustomerDO customerDO = customerDao.selectBank(currentCustomerDO.getId());
        if (customerDO != null) {
            customerDO.setId(currentCustomerDO.getId());
            bankInformationDTO = BankInformationDTO.valueOf(customerDO);
        } else {
            bankInformationDTO = new BankInformationDTO();
        }
        return bankInformationDTO;
    }

    @Override
    public List<CustomerDTO> list() {
        List<CustomerDTO> customerDTOList = new ArrayList<>();
        List<CustomerDO> customerDOList = customerDao.selectAll();
        if (!CollectionUtils.isEmpty(customerDOList)) {
            customerDOList.forEach(customerDO -> customerDTOList.add(CustomerDTO.valueOf(customerDO)));
        }
        return customerDTOList;
    }

    @Override
    public void add(CustomerQuery customerQuery) {

        if(!StringUtils.isEmpty(customerDao.isExistedPhone(customerQuery.getUserMobile()))){
                throw new DuplicatePhoneException();
        }

        CustomerDO customerDO = new CustomerDO();
        customerDO.setUsername(customerQuery.getLoginAccount());
        customerDO.setPassword(EncryptUtil.password(customerQuery.getLoginPassword()));
        customerDO.setTradingPassword(EncryptUtil.password(customerQuery.getTransPassword()));
        customerDO.setName(customerQuery.getUserName());
        customerDO.setPhone(customerQuery.getUserMobile());

        customerDao.insert(customerDO);
    }

    @Override
    public void update(CustomerQuery customerQuery) {
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(customerQuery.getUserId());
        customerDO.setName(customerQuery.getUserName());
        customerDO.setPhone(customerQuery.getUserMobile());
        customerDao.update(customerDO);
    }

    @Override
    public void updateStatus(long id, String status) {
        if (!"ACTIVATE".equals(status) && !"STOP".equals(status)) {
            throw new InvalidParameterException();
        }
        CustomerDO customerDO = new CustomerDO();
        customerDO.setId(id);
        customerDO.setDisable("STOP".equals(status));
        customerDao.updateStatus(customerDO);
    }

    @Override
    public List<CustomerBalanceWithCustomerDTO> balanceListAll() {
        List<CustomerBalanceWithCustomerDTO> customerBalanceWithCustomerDTOList = new ArrayList<>();
        List<CustomerDO> customerDOList = customerDao.selectAllWithBalance();
        if (!CollectionUtils.isEmpty(customerDOList)) {
            customerDOList.forEach(customerDO -> customerBalanceWithCustomerDTOList.add(CustomerBalanceWithCustomerDTO.valueOf(customerDO)));
        }
        return customerBalanceWithCustomerDTOList;
    }

    @Override
    public List<CustomerBaseDTO> baseListAll() {
        List<CustomerBaseDTO> customerBaseDTOList = new ArrayList<>();
        List<CustomerDO> customerDOList = customerDao.selectAllWithBase();
        if (!CollectionUtils.isEmpty(customerDOList)) {
            customerDOList.forEach(customerDO -> customerBaseDTOList.add(CustomerBaseDTO.valueOf(customerDO)));
        }
        return customerBaseDTOList;
    }

    @Override
    public CustomerBaseInformationDTO baseInformation(long id) {
        CustomerBaseInformationDTO customerBaseInformationDTO;
        CustomerDO customerDO = customerDao.selectBase(id);
        if (customerDO == null) {
            throw new InvalidParameterException();
        }
        customerBaseInformationDTO = CustomerBaseInformationDTO.valueOf(customerDO);
        return customerBaseInformationDTO;
    }

    @Override
    public List<CustomerProductWithCustomerDTO> productListAll() {
        List<CustomerProductWithCustomerDTO> customerProductWithCustomerDTOList = new ArrayList<>();
        List<CustomerProductDO> customerProductDOList = customerProductDao.selectAll();
        if (!CollectionUtils.isEmpty(customerProductDOList)) {
            customerProductDOList.forEach(customerProductDO -> customerProductWithCustomerDTOList.add(CustomerProductWithCustomerDTO.valueOf(customerProductDO)));
        }
        return customerProductWithCustomerDTOList;
    }

    @Override
    public void updateProductStatus(long customerProductId, String status) {
        if (!"START".equals(status) && !"STOP".equals(status)) {
            throw new InvalidParameterException();
        }
        CustomerProductDO customerProductDO = new CustomerProductDO();
        customerProductDO.setId(customerProductId);
        customerProductDO.setDisable("STOP".equals(status));
        customerProductDao.updateStatus(customerProductDO);
    }

    @Override
    public void updateProduct(CustomerProductQuery customerProductQuery) {
        CustomerProductDO customerProductDO = new CustomerProductDO();
        customerProductDO.setId(customerProductQuery.getListSn());
        customerProductDO.setName(customerProductQuery.getMinerName());
        customerProductDO.setModel(customerProductQuery.getMinerModel());
        customerProductDO.setPerformance(customerProductQuery.getMinerCountingForce());
        customerProductDao.update(customerProductDO);
    }

    @Override
    public List<CustomerProductApplyDTO> productApplyListAll() {
        List<CustomerProductApplyDTO> customerProductApplyDTOList = new ArrayList<>();
        try {
            List<CustomerProductApplyDO> customerProductApplyDOList = customerProductApplyDao.selectAllWithCustomer();
            if (!CollectionUtils.isEmpty(customerProductApplyDOList)) {
                customerProductApplyDOList.forEach(customerProductApplyDO -> customerProductApplyDTOList.add(CustomerProductApplyDTO.valueOf(customerProductApplyDO)));
            }
        } catch (Exception e) {
            throw new CustomerBuyMinerException("用户矿机申请购买失败");
        }
        return customerProductApplyDTOList;
    }

    @Override
    @Transactional
    public void updateProductApplyStatus(OrderQuery orderQuery) {
        int status = orderQuery.isSuccess() ? ConstantUtil.ORDER_STATUS_COMPLETED : ConstantUtil.ORDER_STATUS_REJECTED;
        CustomerProductApplyDO oldCustomerProductApplyDO = customerProductApplyDao.selectOne(orderQuery.getListSn());

        if (oldCustomerProductApplyDO == null || oldCustomerProductApplyDO.getStatus() != ConstantUtil.ORDER_STATUS_PROCESSING) {
            throw new InvalidParameterException();
        }

        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(orderQuery.getListSn());
        orderDO.setStatus(status);
        orderDao.updateStatus(orderDO);

        if (status == ConstantUtil.ORDER_STATUS_COMPLETED) {
            CustomerProductDO customerProductDO = new CustomerProductDO();
            ProductDO productDO = oldCustomerProductApplyDO.getProductDO();
            customerProductDO.setUserProductSN(RandomStringUtils.randomAlphanumeric(15));
            customerProductDO.setImageUri(productDO.getImageUri());
            customerProductDO.setProductNumber(productDO.getProductNumber());
            customerProductDO.setModel(productDO.getModel());
            customerProductDO.setName(productDO.getName());
            customerProductDO.setPerformance(productDO.getPerformance());
            customerProductDO.setCustomerDO(oldCustomerProductApplyDO.getCustomerDO());
            createCustomerProduct(customerProductDO);
        }
    }

    private void createCustomerProduct(CustomerProductDO customerProductDO){
        try {
            customerProductDao.insert(customerProductDO);
        }catch (DuplicateKeyException e){
            customerProductDO.setUserProductSN(RandomStringUtils.randomAlphanumeric(15));
            createCustomerProduct(customerProductDO);
        }
    }

    @Override
    public void updateProductApplyNote(OrderQuery orderQuery) {
        OrderDO orderDO = new OrderDO();
        orderDO.setOrderSN(orderQuery.getListSn());
        orderDO.setNote(orderQuery.getRemark());
        orderDao.updateStatus(orderDO);
    }

    private void removeBalance(BalanceDTO balanceDTO, String status) {
        if (("TOTAL").equals(status)) {
            balanceDTO.setAvailable(null);
        } else if (("AVAILABLE").equals(status)) {
            balanceDTO.setTotal(null);
        }
    }
}
